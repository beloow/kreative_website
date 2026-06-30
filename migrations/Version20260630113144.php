<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260630113144 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE site_setting ADD seo_home_title VARCHAR(70) NOT NULL, ADD seo_home_description VARCHAR(160) NOT NULL, ADD seo_about_title VARCHAR(70) NOT NULL, ADD seo_about_description VARCHAR(160) NOT NULL, ADD seo_services_title VARCHAR(70) NOT NULL, ADD seo_services_description VARCHAR(160) NOT NULL, ADD seo_contact_title VARCHAR(70) NOT NULL, ADD seo_contact_description VARCHAR(160) NOT NULL, ADD og_image_filename VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE site_setting DROP seo_home_title, DROP seo_home_description, DROP seo_about_title, DROP seo_about_description, DROP seo_services_title, DROP seo_services_description, DROP seo_contact_title, DROP seo_contact_description, DROP og_image_filename');
    }
}
