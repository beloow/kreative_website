<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629150541 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE site_setting ADD site_color_accent VARCHAR(20) NOT NULL, ADD site_color_background VARCHAR(20) NOT NULL, ADD site_font_heading VARCHAR(60) NOT NULL, ADD site_font_body VARCHAR(60) NOT NULL, ADD admin_color_accent VARCHAR(20) NOT NULL, ADD admin_color_background VARCHAR(20) NOT NULL, ADD admin_color_scheme VARCHAR(10) NOT NULL, DROP color_accent, DROP color_background, DROP font_heading, DROP font_body');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE site_setting ADD color_accent VARCHAR(20) NOT NULL, ADD color_background VARCHAR(20) NOT NULL, ADD font_heading VARCHAR(60) NOT NULL, ADD font_body VARCHAR(60) NOT NULL, DROP site_color_accent, DROP site_color_background, DROP site_font_heading, DROP site_font_body, DROP admin_color_accent, DROP admin_color_background, DROP admin_color_scheme');
    }
}
