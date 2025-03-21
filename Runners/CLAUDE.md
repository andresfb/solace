# Development Guide

## Commands
- **Build/Serve**: `composer dev` (runs server, queue, logs, and vite)
- **Lint**: `composer lint` (Laravel Pint)
- **Refactor**: `composer refacto` (Rector)
- **Test All**: `composer test`
- **Single Test**: `composer test:unit -- --filter=TestName`
- **Type Check**: `composer test:types` (PHPStan level 8)

## Code Style
- **PHP Version**: 8.3+
- **Formatting**: 4 spaces indentation, LF line endings
- **Types**: Strong typing with PHPStan level 8
- **Naming**: PSR-12 conventions, descriptive names
- **Error Handling**: Use exceptions with specific classes
- **Architecture**: Follows Laravel conventions with modules
- **Tests**: Pest framework with 100% coverage requirement
- **Imports**: Sort alphabetically
- **Structure**: Follow Laravel MVC pattern
- **Documentation**: PHPDoc for all methods and classes
- **Modules**: Custom module system under `/modules` directory

Always run the full test suite before submitting changes and ensure code passes all linting checks.