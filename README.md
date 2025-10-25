# PASEI Voting System

## Tech Stack
- **Frameworks**: Laravel, Tailwind CSS
- **Tools**: Vite, Laravel Vite Plugin, Laravel Blade
- **Database**: MySQL
- **System Requirements**: PHP 8.2, Node.js 22

## Installation

### Prerequisites
- PHP 8.2
- Node.js 22
- Composer

### Quick Start
```bash
# Clone the repository
git clone github-repo-url

# Navigate to the project directory
cd voting-system

# Install dependencies
composer install

# Install Node.js dependencies
npm install

# Copy .env.example to .env
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed

# Start the development server
php artisan serve
```

## Usage

### Basic Usage
```blade
<!-- Example of a Blade template -->
@extends('layouts.app')

@section('content')
<div class="container">
		<h1>Welcome to the Voting System</h1>
</div>
@endsection
```

### Advanced Usage
- **Admin Dashboard**: Access the admin dashboard to manage elections, candidates, and voters.
- **Voter Interface**: Use the voter interface to cast your vote securely.

## Project Structure
```
voting-system/
├── app/
│   ├── Enums/
│   ├── Http/
│   ├── Models/
│   ├── Providers/
│   └── ...
├── bootstrap/
├── config/
├── database/
├── public/
├── resources/
│   ├── views/
│   └── js/
|		└── css/
├── routes/
├── storage/
├── tests/
├── vendor/
├── .env
├── .env.example
├── .gitignore
├── composer.json
├── package.json
└── README.md
```

## Configuration
- **Environment Variables**: Configure environment variables in the `.env` file.
- **Configuration Files**: Customize configuration files as needed.
- **Customization Options**: Adjust settings in the `config` directory.
