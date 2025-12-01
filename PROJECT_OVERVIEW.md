## Project Overview

### Purpose
- Secure, two-factor and biometric-gated online voting system for PASEI-style elections.
- Supports two user roles: `voter` and `admin` (plus `system-admin`).
- Admins manage elections, positions, candidates, voters, and review votes; voters authenticate and cast ballots during active election windows.

### Core Features
- Admin and Voter authentication with reCAPTCHA + SMS OTP; Admins additionally pass face verification.
- Face recognition (descriptor capture and server-side vector comparison) for admins and voters.
- Election lifecycle management: create upcoming elections, archives, and active window validation.
- Candidate/position management and filtered search, pagination, and CRUD flows.
- Ballot UI with per-position selection limits, review receipt, and final submission.
- OTP delivery via external SMS gateway; verification and one-time-use OTPs.

---

## Tech Stack

### Runtime and Framework
- PHP: ^8.2
- Laravel: ^12.0

### Composer Packages
- anhskohbo/no-captcha: Google reCAPTCHA integration for bot mitigation on login forms.
- guzzlehttp/guzzle: HTTP client used by `OtpService` to call SMS provider.
- intervention/image, jenssegers/imagehash: Image processing and hashing (not deeply used in current scope but available for media features or potential anti-spoofing additions).
- laravel/tinker: Developer REPL.

### NPM / Frontend Tooling
- vite ^7, laravel-vite-plugin ^2: Asset bundling/integration.
- tailwindcss ^4 and @tailwindcss/vite: Utility-first CSS styling.
- axios ^1.11: HTTP client available for front-end interactions (minimal use; native fetch also used).
- concurrently: For dev scripts combining Laravel server, queue worker, logs, and Vite.

---

## Architecture & Design Patterns

### High-Level Structure
- Controllers organized by area: `Admin`, `Auth`, and `Voter` namespaces under `app/Http/Controllers`.
- Single Request class (`SubmitBallotRequest`) encapsulates ballot validation rules and custom post-validation.
- Eloquent models represent core domain: `User`, `Election`, `Position`, `Candidate`, `Vote`, `OtpCode`.
- Services: `OtpService` encapsulates OTP sending and verification logic.
- Support helpers: `AdminHelpers.php` provides procedural domain helpers (assertions and ID generators); `FaceMetric.php` offers vector distance calculations.
- Middleware gates: role-based (`AdminOnly`, `VoterOnly`), OTP gating (`VerifyOtp`), election status (`ActiveElection`), and face verification (`FaceVerified`).

### Patterns Observed
- Service Layer: `OtpService` is a dedicated domain service; external API integration isolated here.
- Validation Layer: `FormRequest` (`SubmitBallotRequest`) for complex ballot validation semantics.
- Helper Functions: Global helper functions for admin assertions and ID generation (transactional + `lockForUpdate`).
- Thin Controllers with Eloquent queries and view orchestration.
- View Components: `resources/views/components/ui` contains reusable UI elements (`modal`, `admin-auth`).

---

## Code Structure & Logic Flow

### Routing
- Defined in `routes/web.php`.
- Public: `Route::view('/', 'home')`.
- Admin routes under `admin` prefix and name `admin.*`:
  - Auth: login, OTP, send-otp, change-password, logout.
  - Protected by `admin` middleware: dashboard, voters, positions, candidates, elections, archives, votes, voter-status, face verification.
- Voter routes under `voter` prefix and name `voter.*`:
  - Auth: login, OTP, send-otp, change-password, logout.
  - Protected by `voter` middleware: ballot display/submit, face verification.

### Controllers and Request Handling
- Admin
  - `AdminAuthController`: Login with `admin_id` + password + reCAPTCHA; sends OTP; verifies OTP; optional change password flow requiring OTP.
  - `DashboardController`: Aggregates stats; builds charts per position for the active election.
  - `AdminController`: Manage admin users; system-admin can add admins with face descriptor capture; soft-deactivate via `is_active=false`.
  - `PositionController`, `ElectionController`, `CandidateController`: Standard CRUD with admin credential re-auth embedded in forms via `x-ui.admin-auth` block and `assert_admin_credentials`.
  - `VoteController`: List votes; voter-status with `has_voted` sort.
  - `AdminFaceController`: Enforces admin face verification via face-api.js capture and server-side descriptor comparison.
- Auth (Shared concepts)
  - `VoterAuthController`: Voter login with `member_id` + password + reCAPTCHA; OTP verification; password change requiring OTP; logout.
  - `FaceEnrollController`: Endpoint to store a numeric 128-length face descriptor array for the authenticated user.
- Voter
  - `BallotController`:
    - `showBallot`: Fetches active election by date and time window, prevents re-votes, loads positions with candidates scoped to the election.
    - `submit`: Re-gates active election server-side; blocks double voting; persists `Vote` entries and sets `user.has_voted = true` in a transaction.
  - `VoterFaceController`: Face verification for voters using the same descriptor matching approach.

### Validation
- `SubmitBallotRequest` ensures:
  - `election_id` exists.
  - `positions` payload shape is `[position_id => [candidate_id...]]`.
  - Enforces `maximum_votes` per position.
  - Ensures each candidate belongs to the given election and position.

### Models & Relations
- `User` (Authenticatable)
  - Casts: `password` hashed, `has_voted`/`is_active` boolean, `face_descriptor` array, sign-in/out datetimes.
  - Relations: `votes()`, `otpCodes()`.
- `Election`
  - Attributes: `title`, `date`, `start_time`, `end_time`, `is_active`.
  - Relations: `candidates()`, `votes()`.
- `Position`
  - Attributes: `name`, `maximum_votes`.
  - Relations: `candidates()`.
- `Candidate`
  - Attributes: `election_id`, `position_id`, names, `organization_name`.
  - Relations: `position()`, `election()`, `votes()`.
- `Vote`
  - Attributes: `election_id`, `position_id`, `candidate_id`, `user_id`.
  - Unique constraint on `(election_id, position_id, user_id)` prevents multiple votes for same position.
- `OtpCode`
  - OTP lifecycle with `expires_at` and `used` flag.

### Services, Helpers, Traits
- `OtpService`:
  - `sendOTP(User)`: Generates 6-digit code, sends via SMS API (`services.iprog_sms.api_key`), stores `OtpCode` with expiration.
  - `verifyOtp(User, string)`: Fetches latest unused OTP and marks used if matches.
- `AdminHelpers.php`:
  - `assert_current_user_is_admin()`: 403 unless user type is admin or system-admin.
  - `assert_admin_credentials(adminId, password)`: Validates re-auth pair; restricts to admin/system-admin.
  - `generate_admin_id()` / `generate_member_id()`: Transactional sequence with prefix and zero-padded numeric counter.
- `FaceMetric`:
  - `euclidean(array, array)`, `cosine(array, array)`: Distances for face descriptor comparison; threshold used server-side (`0.6`).

---

## Database Layer

### Migrations
- `users` table: auth identifiers (`admin_id`, `member_id`), names, `password`, `phone_number`, optional `organization_name`, enum `type` (voter|admin|system-admin), flags (`has_voted`, `is_active`), last sign-in/out timestamps, `remember_token`.
- `users.face_descriptor` added as nullable JSON (128-length float vector expected at runtime).
- `sessions` table for session storage.
- `elections`: `title`, `date`, `start_time`, `end_time`, `is_active`, soft deletes.
- `positions`: `name`, `maximum_votes`, soft deletes.
- `candidates`: FK to `elections` and `positions`, names, `organization_name`, soft deletes.
- `votes`: FKs to `elections`, `positions`, `candidates`, `users`; unique constraint on (election, position, user).
- `otp_codes`: FK to `users`, phone, `code`, `expires_at`, `used`.

### Seeders
- `UserSeeder`: Bootstraps system-admins (`sadm0000`, `sadm0001`), admins (`adm0000`, `adm0001`), and a sample voter (`psi0000`).
- `PositionSeeder`: Seeds typical organization roles.
- `ElectionSeeder`: Creates a current active election (date=now, start now-5m, end now+2h).
- `CandidateSeeder`: Seeds candidates per position for the seeded election with randomized names and organizations.

---

## View Layer

### Layouts
- `layouts.app`: Admin shell with sidebar navigation and a change-password modal; uses Tailwind and SweetAlert; global flash handling; includes Chart.js for dashboard.
- `layouts.voter-app`: Voter shell with avatar menu, change-password modal, and SweetAlert flash handling.
- `layouts.auth` / `layouts.voter-auth`: Auth pages for admin and voter login/OTP/face flows with NoCaptcha widgets.

### Components
- `components/ui/modal`: Generic modal with optional form, CSRF, and spoofed methods.
- `components/ui/admin-auth`: Inline admin re-auth block with `admin_id` and `password` inputs for sensitive actions.

### Screens (Selected)
- Admin: dashboard with per-position charts, CRUD pages for elections, positions, candidates, voters, archives list, votes, voter-status.
- Auth: admin login with reCAPTCHA, OTP verification, admin face verification; voter equivalents.
- Voter: ballot stepper UI with per-position selection caps, skip support, receipt review, and JS-based submit + thank-you modal.

---

## Technical Features

### Middleware
- `AdminOnly` / `VoterOnly`: Role gating; redirects unauthenticated to respective login routes.
- `VerifyOtp`: Ensures OTP is verified in session; redirects to OTP routes else.
- `ActiveElection`: Ensures an active election exists for certain flows (not heavily attached in routes but provided as alias).
- `FaceVerified`: Ensures face-verified session flag for restricted areas (alias `face`).

### Security Controls
- reCAPTCHA v2 on both admin and voter login forms via `anhskohbo/no-captcha`.
- SMS OTP for 2FA with single-use codes; admin and voter change-password flows require OTP.
- Face recognition via client-side `face-api.js` models and server-side float-vector comparison with threshold.
- Admin-sensitive CRUD forms require inline re-auth (`admin_id` + password) verified server-side.

### Jobs/Queues
- Queue config is present via default Laravel migration; no custom jobs/listeners included in scope. Dev script includes `queue:listen` runner.

---

## Insights & Recommendations

### Strengths
- Clear separation of concerns between auth flows, voting flows, and admin management.
- Strong multi-factor authentication (reCAPTCHA + OTP + face for admin) improves security.
- Ballot validation robustly enforces per-position caps and candidate-to-election constraints.
- Helper functions encapsulate admin re-auth and ID generation with DB transactional guarantees.

### Areas for Improvement
- OTP Expiry Enforcement: `OtpService::verifyOtp` currently ignores `expires_at`; re-enable expiry check to prevent reuse after timeout.
- Middleware Usage Consistency: Consider enforcing `VerifyOtp` and `FaceVerified` or a dedicated guard consistently across routes that require them (e.g., admin face verification gating before dashboard).
- Election Time Columns: Use proper `date` and `time` column types instead of strings for `elections.date/start_time/end_time` to ease comparisons and indexing.
- Soft Deletes: Ensure application logic accounts for soft-deleted `positions/candidates` where relevant; add global scopes if needed.
- Password Defaults: Avoid hardcoded default passwords; enforce random initial passwords and force change on first login.
- Descriptor Storage: `User.face_descriptor` stored as JSON; consider vector normalization and storing as `float[]` with length validation at model-level or database constraint where possible.
- Security Headers & CSRF: Already present for forms; consider adding Content Security Policy and other headers (via middleware) for face-api and SweetAlert script sources.
- Test Coverage: Add feature tests for ballot submission, OTP verification, face verification pass/fail, role-gating, and admin re-auth flows.
- Rate Limiting: Add throttling on OTP send endpoints and login/OTP verification to mitigate brute-force.
- Audit Trail: Consider logging admin CRUD and vote submissions (already deducible by `Vote` rows, but add explicit audit logs).

---

## Code Snippets

### Example: Ballot payload sent by the front-end
```php
// POST /voter/ballot/submit
positions[<position_id>][] = <candidate_id>
// validated by SubmitBallotRequest
```

### Example: Enforcing per-position maximum in validation
```php
if (count($candidateIds) > $position->maximum_votes) {
  $v->errors()->add("positions.$positionId", "You can only select up to {$position->maximum_votes} candidate(s) for {$position->name}.");
}
```

### Example: Face comparison
```php
$distance = FaceMetric::euclidean($live, $saved);
$pass = $distance <= 0.6;
```

