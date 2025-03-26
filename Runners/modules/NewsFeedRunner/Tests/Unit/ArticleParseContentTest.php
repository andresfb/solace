<?php

declare(strict_types=1);

namespace Modules\NewsFeedRunner\Tests\Unit;

use Modules\NewsFeedRunner\Models\Article\Article;
use ReflectionException;

test('parseContent returns description when content is empty', function (): void {
    // Arrange
    $article = new Article();
    $article->content = '';
    $article->description = 'Sample description';

    // Act
    $result = invokePrivateMethod($article, 'parseContent');

    // Assert
    expect($result)->toBe('Sample description');
});

test('parseContent returns content when description is empty', function (): void {
    // Arrange
    $article = new Article();
    $article->content = 'Sample content';
    $article->description = '';

    // Act
    $result = invokePrivateMethod($article, 'parseContent');

    // Assert
    expect($result)->toBe('Sample content');
});

test('parseContent returns content when both are identical', function (): void {
    // Arrange
    $article = new Article();
    $article->content = 'Identical text';
    $article->description = 'Identical text';

    // Act
    $result = invokePrivateMethod($article, 'parseContent');

    // Assert
    expect($result)->toBe('Identical text');
});

test('parseContent returns description when content ends with ellipsis', function (): void {
    // Arrange
    $article = new Article();
    $article->content = 'Sample content...';
    $article->description = 'Full description without truncation';

    // Act
    $result = invokePrivateMethod($article, 'parseContent');

    // Assert
    expect($result)->toBe('Full description without truncation');
});

test('parseContent returns content when description ends with ellipsis', function (): void {
    // Arrange
    $article = new Article();
    $article->content = 'Full content without truncation';
    $article->description = 'Sample description...';

    // Act
    $result = invokePrivateMethod($article, 'parseContent');

    // Assert
    expect($result)->toBe('Full content without truncation');
});

test('parseContent returns longer content when both start the same', function (): void {
    // Arrange
    $article = new Article();
    $article->content = 'Same start but longer content with more details';
    $article->description = 'Same start but shorter';

    // Act
    $result = invokePrivateMethod($article, 'parseContent');

    // Assert
    expect($result)->toBe('Same start but longer content with more details');
});

test('parseContent combines content and description when content is shorter', function (): void {
    // Arrange
    $article = new Article();
    $article->content = 'Short content';
    $article->description = 'Different longer description with more information';

    // Act
    $result = invokePrivateMethod($article, 'parseContent');

    // Assert
    expect($result)->toBe("Short content\n\nDifferent longer description with more information");
});

test('parseContent combines description and content when description is shorter', function (): void {
    // Arrange
    $article = new Article();
    $article->content = 'Different longer content with more information';
    $article->description = 'Short description';

    // Act
    $result = invokePrivateMethod($article, 'parseContent');

    // Assert
    expect($result)->toBe("Short description\n\nDifferent longer content with more information");
});

test('parseContent cleans up whitespace and formatting', function (): void {
    // Arrange
    $article = new Article();
    $article->content = "Content with  multiple   spaces\nand\n\n\nextra\tlines";

    // Act
    $result = invokePrivateMethod($article, 'parseContent');

    // Assert
    expect($result)->toBe("Content with multiple spaces\nand\nextra lines");
});

/**
 * Helper function to access private/protected methods for testing.
 *
 * @param object $object
 * @param string $methodName
 * @param array $parameters
 * @return mixed
 * @throws ReflectionException
 */
function invokePrivateMethod(object $object, string $methodName, array $parameters = []): mixed
{
    $reflection = new \ReflectionClass(get_class($object));
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(true);
    return $method->invokeArgs($object, $parameters);
}
