# Changelog

All notable changes to `laravel-api-language` will be documented in this file.

## Unreleased

- Fix the Psalm GitHub Actions workflow to use the supported Composer cache action.

## 1.2.0 - 2026-05-27

- Add `LanguageNegotiator` and `LanguageNegotiationResult` for reusable Accept-Language parsing.
- Respect `q=0` language exclusions and expose accepted/excluded locales on the request.
- Add automatic `Vary: Accept-Language` handling to `AcceptLanguageMiddleware`.
- Preserve existing `Content-Language` headers in `ContentLanguageMiddleware`.
- Add the missing `LaravelApiLanguageFacade`.

## 1.0.0 - 202X-XX-XX

- initial release
