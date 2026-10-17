<?php

use PHPUnit\Framework\TestCase;

/**
 * PrestaShop Addons packaging rules that can be checked from the source:
 * an explicit compatibility range, the root .htaccess, matching versions.
 */
class ConferbotPackageTest extends TestCase
{
    private static function source($file)
    {
        return file_get_contents(dirname(__DIR__) . '/' . $file);
    }

    public function testCompatibilityRangeIsExplicit()
    {
        $php = self::source('conferbot.php');
        $this->assertSame(1, preg_match('/ps_versions_compliancy\s*=\s*array\((.*?)\);/s', $php, $m));
        $this->assertStringNotContainsString('_PS_VERSION_', $m[1]);
        $this->assertMatchesRegularExpression("/'max'\s*=>\s*'9\.99\.99'/", $m[1]);
        $this->assertMatchesRegularExpression("/'min'\s*=>\s*'1\.7\.0\.0'/", $m[1]);
    }

    public function testRootHtaccessDeniesPhpOnApache22And24()
    {
        $htaccess = self::source('.htaccess');
        $this->assertStringContainsString('<Files *.php>', $htaccess);
        $this->assertStringContainsString('Require all denied', $htaccess);
        $this->assertStringContainsString('Deny from all', $htaccess);
    }

    public function testModuleAndConfigXmlVersionsMatch()
    {
        $this->assertSame(1, preg_match("/\\\$this->version\s*=\s*'([0-9.]+)'/", self::source('conferbot.php'), $php));
        $xml = simplexml_load_string(self::source('config.xml'));
        $this->assertSame($php[1], trim((string) $xml->version));
    }
}
