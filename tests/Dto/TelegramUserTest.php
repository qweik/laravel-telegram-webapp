<?php

namespace Micromagicman\TelegramWebApp\Tests\Dto;

use Micromagicman\TelegramWebApp\Dto\TelegramUser;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use ReflectionProperty;

class TelegramUserTest extends TestCase
{

    /**
     * @throws ReflectionException - if getter is absent in {@link TelegramUser}
     */
    #[Test]
    public function testUserDtoHasGetterOnEachField()
    {
        $testUserData = [
            'id' => 111111111,
            'first_name' => 'Evgen',
            'last_name' => 'Evgen',
            'username' => 'micromagicman',
            'language_code' => 'xx',
            'is_premium' => true,
            'allows_write_to_pm' => true,
        ];
        $testUser = new TelegramUser( $testUserData );

        $reflection = new ReflectionClass( TelegramUser::class );
        foreach ( $reflection->getProperties() as $property ) {
            if ( !$property->isStatic() ) {
                $propertyType = $property->getType();
                $propertyName = $property->getName();
                $expectedValue = $testUserData[ $propertyName ];
                $methodPrefix = 'bool' === $propertyType->getName() ? 'is' : 'get';
                $propertyCamelCase = $this->snakeCaseToCamelCase( mb_strtolower( $property->getName() ) );
                $methodName = str_starts_with( $propertyCamelCase, 'is' )
                    ? $propertyCamelCase
                    : $methodPrefix . ucfirst( $propertyCamelCase );
                $method = $reflection->getMethod( $methodName );
                $this->assertEquals( $expectedValue, $method->invoke( $testUser ) );
            }
        }
    }

    /**
     * Telegram keeps adding keys to the user object. Assigning them blindly created
     * dynamic properties, deprecated since PHP 8.2 and an error in PHP 9.
     */
    #[Test]
    public function testUnknownTelegramFieldsAreIgnored()
    {
        $testUser = new TelegramUser( [
            'id' => 111111111,
            'first_name' => 'Evgen',
            'photo_url' => 'https://t.me/i/userpic/320/evgen.jpg',
            'added_to_attachment_menu' => true,
        ] );

        $propertyNames = fn( array $properties ) => array_map(
            fn( ReflectionProperty $property ) => $property->getName(),
            $properties
        );

        $this->assertEquals( 111111111, $testUser->getId() );
        $this->assertEquals(
            [],
            array_diff(
                $propertyNames( ( new ReflectionObject( $testUser ) )->getProperties() ),
                $propertyNames( ( new ReflectionClass( TelegramUser::class ) )->getProperties() )
            ),
            'Unknown Telegram fields were assigned as dynamic properties.'
        );
    }

    /**
     * Only id and first_name are guaranteed by Telegram. The rest used to be left
     * uninitialized, and reading one threw rather than returning an empty value.
     */
    #[Test]
    public function testOptionalTelegramFieldsFallBackToEmptyValues()
    {
        $testUser = new TelegramUser( [ 'id' => 111111111, 'first_name' => 'Evgen' ] );

        $this->assertEquals( '', $testUser->getLastName() );
        $this->assertEquals( '', $testUser->getUsername() );
        $this->assertEquals( '', $testUser->getLanguageCode() );
        $this->assertFalse( $testUser->isPremium() );
        $this->assertFalse( $testUser->isAllowsWriteToPm() );
    }

    private function snakeCaseToCamelCase( string $source ): string
    {
        return lcfirst( str_replace( ' ', '', ucwords( str_replace( '_', ' ', $source ) ) ) );
    }
}