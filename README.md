# 💬 EasyList Forums

## 📑 Table of Contents

- [❓ What are the EasyList Forums?](#-what-are-the-easylist-forums)
- [🛠️ Requirements](#️-requirements)
- [🚀 Getting Started](#-getting-started)
  - [🐳 Running with Docker](#-running-with-docker)
- [📁 Directory Structure](#-directory-structure)
- [💡 Tips](#-tips)
- [📝 License](#-license)

Welcome to the **forums.lanik.us** repository!

## ❓ What are the EasyList Forums?

The EasyList Forums, hosted at [forum.lanik.us](https://forum.lanik.us/), serve
as the official hub for the EasyList ad-blocking community. Here, users report
ad-blocking issues, discuss filter list maintenance, and propose new rules for
EasyList, EasyPrivacy, and related lists.

## 🛠️ Requirements

To run this project, you will need:

- **PHP**: `^7.2` or `^8.0` (as defined in `composer.json`)
- **Web Server**: Apache or Nginx
- **Database**: MySQL, MariaDB, or PostgreSQL
- **Composer**: PHP dependency manager

## 🚀 Getting Started

1. **Clone the repository:**

   ```bash
   git clone git@github.com:LanikSJ/forums.lanik.us.git
   cd forums.lanik.us
   ```

2. **Install dependencies:**

   ```bash
   composer install
   ```

3. **Configure the environment:**

   Create a `config.php` file at the root of the repository. Do not commit this
   file as it contains sensitive database credentials.

4. **Directory Permissions:**

   Ensure the web server has write access to the following directories:
   - `cache/`
   - `files/`
   - `store/`
   - `images/avatars/upload/`

### 🐳 Running with Docker

Alternatively, you can run the project using Docker and Docker Compose:

1. **Configure the database host:**

   In your `config.php` file, ensure the database host is configured to connect to the database container:

   ```php
   $dbhost = 'db';
   ```

2. **Build and start the services:**

   ```bash
   docker compose up -d --build
   ```

3. **Access the forum:**

   Open your browser and go to `http://localhost:8080`.

## 📁 Directory Structure

- **`adm/`**: The administration control panel.
- **`assets/`**: Assets like CSS, JavaScript, and fonts.
- **`bin/`**: Command-line interface scripts.
- **`styles/`**: Custom and default phpBB styles (themes).
- **`vendor/`**: Composer dependencies.

## 💡 Tips

- Do not commit the `config.php` file or files inside the `cache/`, `files/`,
  and `store/` directories.
- Ensure PHP extensions `json` and `mbstring` are enabled on your server.

## 📝 License

[GPL-2.0-only License](LICENSE) © phpBB Limited / LanikSJ
