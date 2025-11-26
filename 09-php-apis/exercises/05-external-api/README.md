# Exercise 9.5 - Consume External API

## Objective

Learn to consume and integrate external APIs into your PHP application.

## Duration

2-3 hours

## Task

Build an application that fetches data from external APIs and displays it.

## Requirements

- [ ] Consume at least 2 external APIs:
  - Weather API (OpenWeatherMap, WeatherAPI)
  - Public API (GitHub, JSONPlaceholder, etc.)
- [ ] Use cURL or Guzzle for HTTP requests
- [ ] Handle API errors gracefully
- [ ] Cache API responses (optional)
- [ ] Display data in user-friendly format
- [ ] Handle rate limiting
- [ ] Implement error retry logic

## Suggested APIs

**Free APIs to use:**
- JSONPlaceholder: https://jsonplaceholder.typicode.com
- OpenWeatherMap: https://openweathermap.org/api
- GitHub API: https://api.github.com
- REST Countries: https://restcountries.com
- Dog API: https://dog.ceo/dog-api

## Starter Files

Work in `weather.php`, `github.php`, `api-client.php` - see starter code there.

## Making API Requests

**Using cURL:**
```php
$ch = curl_init('https://api.github.com/users/octocat');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'PHP App');
$response = curl_exec($ch);
$data = json_decode($response, true);
curl_close($ch);
```

**Using file_get_contents:**
```php
$opts = ['http' => ['header' => 'User-Agent: PHP App']];
$context = stream_context_create($opts);
$response = file_get_contents($url, false, $context);
$data = json_decode($response, true);
```

## Example Features

**Weather App:**
- Get current weather for a city
- Display temperature, conditions, humidity
- Show 5-day forecast
- Handle city not found errors

**GitHub Profile Viewer:**
- Fetch user profile by username
- Display repos, followers, following
- Show recent activity
- Handle user not found

## Checklist

- [ ] API requests work correctly
- [ ] JSON responses parsed
- [ ] Error handling implemented
- [ ] API keys stored securely (if needed)
- [ ] Rate limiting respected
- [ ] User-friendly error messages
- [ ] Data displayed nicely

## Tips

- Store API keys in environment variables
- Always check for errors: `if (!$response) { ... }`
- Parse JSON: `json_decode($response, true)`
- Set timeouts for requests
- Handle HTTP status codes
- Cache responses to reduce API calls
- Read API documentation carefully
- Respect rate limits
- Use proper User-Agent headers
