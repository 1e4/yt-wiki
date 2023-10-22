# Table of Contents

1. [Installation](#installation)
2. [Exercise](#exercise-introduction)
    1. [Task Requirements](#task-requirements)
3. [Technical Reasoning](#technical-reasoning)
    1. [Docker](#no-docker)
    2. [Database](#no-database)
    3. [Tests](#tests)
    4. [Invokable Controllers](#invokable-controllers)
    5. [Takeaways](#things-i-took-away-from-this)
4. [Improvements](#improvements)

## Installation

```bash
cp .env.example .env # Fill in .env such as Youtube API Key
composer install
php artisan app:fetch-wikipedia
php artisan app:fetch-youtube
php artisan serve
```

After those you should also add a cron if you want the data to be refreshed

```
* * * * * php /path/to/project schedule:run 1>> /dev/null 2>&1
```

Visit the URL `php artisan serve` prints out and head to `/nl` or any other country code that is
in `config/country_codes.php` you can also append `limit` `page` or `offset` query parameters for example `http://localhost:8080/nl?page=5`

## Exercise Introduction

In this exercise your task consists of talking to two different data sources, merging the data into a coherent responses
and returning it with a couple of niceties.

### Task Requirements

- Laravel or any other PHP framework
- PHP 8.1

Optionally

- Docker/Docker compose

Apart from that you have the freedom to use any library. It is recommended you use framework functionality whenever
possible.

Going on to the task at hand:

1. You need to fetch from the YouTube API the most popular videos for the following countries
    - uk
    - nl
    - de
    - fr
    - es
    - it
    - gr

The important information is the description as well as the normal and high resolution thumbnails. Take care not to
trigger rate limits any way you see fit.

2. For the above countries, fetch from the Wikipedia API the initial paragraphs of their Wikipedia articles (before the
   sections).
3. Enrich each country with the fetched data that was collected from Wikipedia and YouTube results
4. Return the results in JSON. You should be able to ask for offset, page and country
5. Apply caching where necessary

## Technical Reasoning

While on the basis of reading this task it may seem simple, but as is the problem with many simple tasks, there are a
million and one ways to accomplish the task, so below is also reasoning as to the choices I chose

#### Getting data with cronjobs rather than when requested

Hitting an API can be unpredictable and will slow response times down dramatically, so I chose to use Cronjobs to fetch
data twice a day, this also avoids hitting any rate limits

#### No docker?

I don't really know Docker, and while it would maybe help, this application is very simple, there is one route, no
database, only caching

#### No Database?

It's completely overkill for the scenario. If we wanted to scale it or monitor for changes for popular videos then yes a
database would be needed, but caching is all I believe that is needed for this task

#### Tests?

Yep, an improvement to this would be TDD, but time restraints limit that and given the simplicity of the application
there are probably only a few edge cases where the application may crash

#### Invokable controllers?

Most of the logic to get the data is in the cronjobs, the controller should only really prepare the response - I could
even cache this and return it but I don't feel such micro optimisations are needed here

#### Things I took away from this

Macros are cool and fun. A lot of Laravel can be extended with Macros and I felt the Http client was a perfect place to
use these rather than polluting the commands more than they already are. The Http client is also a nice wrapper around
Guzzle, I've never personally used it before this, I just invoked Guzzle instead.

### Improvements

There are a million ways to write this as I mentioned earlier and probably a thousand ways to improve the application
given more time. Things I would probably change if this ever went live

- Tests, mock responses from Youtube and Wikipedia
- Better error handling incase an API dies - I'm working on the assumption it will always work, if it doesn't then the
  cache is still there
- Extrapolate the Macros into their own Service Provider and make it deferred so it's not booting on every request
- I'd probably use the Youtube API package, but I didn't want to use any additional packages, I feel it would defeat the
  point in the task at hand, anyone can just stick packages together to make something, but I feel the point of this
  assignment is more to use the framework. Using a package would certainly clean up some code, especially when it comes
  to pagination
- Could extrapolate from logic away from commands into a Service such as Services/Youtube to clean up some code
- Any API going intro production should be based on [OpenAPI](https://spec.openapis.org/oas/v3.0.3)

Overall I believe the application fits the task assigned, but as mentioned, for those that spend days on it you could
expand it quite a but with new features and improvements
