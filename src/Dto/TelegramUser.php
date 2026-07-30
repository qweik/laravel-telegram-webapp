<?php

namespace Micromagicman\TelegramWebApp\Dto;

/**
 * Description of Telegram WebApp user
 */
class TelegramUser
{
    /**
     * A unique identifier for the user.
     * @var int
     */
    private int $id;

    /**
     * First name of the user.
     * @var string
     */
    private string $first_name;

    /**
     * Last name of the user. Optional in the Telegram payload.
     * @var string
     */
    private string $last_name = '';

    /**
     * Username of the user. Optional in the Telegram payload.
     * @var string
     */
    private string $username = '';

    /**
     * Telegram user's current language as 2-char code. Optional in the Telegram payload.
     * @var string
     */
    private string $language_code = '';

    /**
     * true, if this user is a Telegram Premium user. Absent for non-premium users.
     */
    private bool $is_premium = false;

    /**
     * true, if this user allowed the bot to message them. Optional in the Telegram payload.
     * @var bool
     */
    private bool $allows_write_to_pm = false;

    /**
     * Only fields declared above are taken. Telegram keeps adding keys to the user
     * object (photo_url, added_to_attachment_menu, ...), and assigning those blindly
     * created dynamic properties, deprecated since PHP 8.2 and an error in PHP 9.
     *
     * @param array<string, mixed> $telegramUserData
     */
    public function __construct( array $telegramUserData )
    {
        foreach ( $telegramUserData as $key => $value ) {
            if ( property_exists( $this, $key ) ) {
                $this->{$key} = $value;
            }
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->first_name;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->last_name;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->language_code;
    }

    /**
     * @return bool
     */
    public function isPremium(): bool
    {
        return $this->is_premium;
    }

    /**
     * @return bool
     */
    public function isAllowsWriteToPm(): bool
    {
        return $this->allows_write_to_pm;
    }
}