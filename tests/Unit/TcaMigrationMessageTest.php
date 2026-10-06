<?php

declare(strict_types=1);

namespace Calien\Typo3DeprecationCauser\Tests\Unit;

use Calien\Typo3DeprecationCauser\TcaMigrationMessage;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TcaMigrationMessage::class)]
final class TcaMigrationMessageTest extends TestCase
{
    /**
     * @param list<array{string, string|null}> $expected
     */
    #[Test]
    #[DataProvider('messageProvider')]
    public function namesTheMigratedTablesAndFields(string $message, array $expected): void
    {
        self::assertSame($expected, TcaMigrationMessage::migratedItems($message));
    }

    public static function messageProvider(): Generator
    {
        $context = 'Automatic TCA migration done during bootstrap. Please adapt TCA accordingly, these migrations'
            . ' will be removed. The backend module "Configuration -> TCA" shows the modified values.'
            . ' Please adapt these areas:';
        yield 'field of table' => [
            'message' => $context . "\nThe TCA field 'starttime' of table 'tx_acme_item' defines renderType=\"inputDateTime\".",
            'expected' => [['tx_acme_item', 'starttime']],
        ];
        yield 'field in table' => [
            'message' => $context . "\nThe TCA field 'title' in table 'tx_acme_item'\" defines an option.",
            'expected' => [['tx_acme_item', 'title']],
        ];
        yield 'column path' => [
            'message' => $context . "\nThe TCA \"tx_acme_item['columns']['images']['config']['type'] = 'none'\" is migrated.",
            'expected' => [['tx_acme_item', 'images']],
        ];
        yield 'column override' => [
            'message' => $context . "\nThe TCA column override 'bodytext' of table 'tt_content' uses an option.",
            'expected' => [['tt_content', 'bodytext']],
        ];
        yield 'field of quoted table' => [
            'message' => $context . "\nBecause the field 'pid' of 'tx_acme_item' is considered as a system field.",
            'expected' => [['tx_acme_item', 'pid']],
        ];
        yield 'ctrl option of table' => [
            'message' => $context . "\nThe TCA ['ctrl']['cruser_id'] of table 'tx_acme_item' is not evaluated anymore.",
            'expected' => [['tx_acme_item', null]],
        ];
        yield 'table configuration' => [
            'message' => $context . "\nThe 'tx_acme_item' TCA configuration 'searchFields' is migrated.",
            'expected' => [['tx_acme_item', null]],
        ];
        yield 'several lines' => [
            'message' => $context
                . "\nThe TCA field 'starttime' of table 'tx_acme_item' defines renderType=\"inputDateTime\"."
                . "\nThe TCA table 'tx_vendor_item' defines [ctrl][shadowColumnsForNewPlaceholders].",
            'expected' => [['tx_acme_item', 'starttime'], ['tx_vendor_item', null]],
        ];
        yield 'other deprecation' => [
            'message' => "The TCA field 'starttime' of table 'tx_acme_item' is used somewhere.",
            'expected' => [],
        ];
    }
}
