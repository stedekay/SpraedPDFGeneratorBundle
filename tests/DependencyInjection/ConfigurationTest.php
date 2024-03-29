<?php

namespace Spraed\PDFGeneratorBundle\Tests\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Spraed\PDFGeneratorBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration(): Configuration
    {
        return new Configuration();
    }

    public function testEmptyConfigurationIsValid(): void
    {
        $this->assertConfigurationIsValid(array());
    }

    public function testCommandEnvironmentCanBeSpecified(): void
    {
        $this->assertConfigurationIsValid(
            array(
                'spraed_pdf_generator' => array(
                    'command' => array(
                        'env' => array(
                            'foo' => 'bar'
                        )
                    )
                )
            )
        );
    }

    public function testJavaFullPathCanBeConfigured(): void
    {
        $this->assertConfigurationIsValid(
            array(
                'spraed_pdf_generator' => array(
                    'java' => array(
                        'full_pathname' => '/bin/java'
                    )
                )
            )
        );
    }
}
