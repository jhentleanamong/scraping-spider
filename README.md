# Scraping Spider

## Introduction

Scraping Spider is a web scraping API application designed to simplify the process of extracting data from websites, offering a RESTful API interface that allows users to specify the target URL and parameters for scraping.

## Built With

- [![Tailwind CSS][tailwindcss.com]][tailwindcss-url]
- [![Alpine.js][alpinejs.dev]][alpinejs-url]
- [![Laravel][laravel.com]][laravel-url]
- [![Laravel Livewire][livewire.laravel.com]][livewire.laravel-url]

## Screenshots

Below are some screenshots to give you a preview of the Web Scraping API tool in action:

| Dashboard | API Request Builder 
| --- | --- |
| ![Dashboard](/resources/images/screenshots/dashboard.png) | ![API Request Builder](/resources/images/screenshots/api-request-builder.png)

## Usage

To submit a scraping job, send a GET request to `/api/v1` with the following parameters:

- api_key: Your api key.
- url: The URL of the page you want to scrape.
- extract_rules: Data extraction from CSS selectors.

## Contributing

Contributions are welcome! Please feel free to submit pull requests or open issues to suggest improvements.

## License

Scraping Spider is open-sourced software licensed under the MIT license.

## Author

- [Jhentle Anamong](https://www.linkedin.com/in/jhentle-anamong/)

<!-- MARKDOWN LINKS & IMAGES -->
[tailwindcss.com]: https://img.shields.io/badge/Tailwind_CSS-000000?style=for-the-badge&logo=tailwindcss&logoColor=white
[tailwindcss-url]: https://tailwindcss.com/

[alpinejs.dev]: https://img.shields.io/badge/Alpine.js-4A4A55?style=for-the-badge&logo=alpine.js&logoColor=FF3E00
[alpinejs-url]: https://alpinejs.dev/

[laravel.com]: https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white
[laravel-url]: https://laravel.com

[livewire.laravel.com]: https://img.shields.io/badge/Livewire-0769AD?style=for-the-badge&logo=livewire&logoColor=white
[livewire.laravel-url]: https://livewire.laravel.com/
