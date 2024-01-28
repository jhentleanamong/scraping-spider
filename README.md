# Easy Scraper

## Overview
This project is a Laravel-based backend application designed to manage and process jobs with RESTful API endpoints. It uses Redis for data storage and supports background processing for web scraping tasks.

## Features
- **RESTful API Endpoints:**
    - List all jobs
    - Create a job
    - Retrieve job by ID
    - Delete job by ID
- **Redis Data Store:** Utilizes Redis to maintain job details, statuses, and scraped data.
- **Background Processing:** Implements Laravel queues for asynchronous web scraping tasks.

## Requirements
- PHP 8.1+
- Laravel v10.0+
- Redis Server

## API Endpoints

### List All Jobs
- **Method:** GET
- **Endpoint:** `/v1/api/jobs`

### Create a Job
- **Method:** POST
- **Endpoint:** `/v1/api/jobs`

### Retrieve Job by ID
- **Method:** GET
- **Endpoint:** `/v1/api/jobs/{id}`

### Delete Job by ID
- **Method:** DELETE
- **Endpoint:** `/v1/api/jobs/{id}`
