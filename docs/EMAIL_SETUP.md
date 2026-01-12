## Gmail SMTP configuration

Set these in your `.env` (restart the queue/HTTP worker after changing env):

```
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=youraddress@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=youraddress@gmail.com
MAIL_FROM_NAME="PASEI Voting System"
```

Notes:
- You must generate a Gmail App Password (Google Account → Security → 2‑Step Verification → App passwords) and use it as `MAIL_PASSWORD`.
- If you are using Google Workspace, ensure SMTP is allowed.
- In production, do not commit your `.env`.

## Running migrations

```
php artisan migrate
```

This creates the `password_reset_tokens` table and adds the nullable `email` column to `users`.

## Testing the flow

1. Ensure `users.email` is populated for your account (admin or voter).
2. Visit the appropriate login page and click “Forgot password?”.
3. Submit your email; you should receive the reset email.
4. Click “Reset your password” in the email, complete the form, then log in with the new password.




