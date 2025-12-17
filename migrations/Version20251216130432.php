<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251216130432 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news_articles ADD language VARCHAR(2) NOT NULL');
        $this->addSql('ALTER TABLE news_articles ALTER url TYPE VARCHAR(500)');
        $this->addSql('ALTER TABLE news_articles ALTER url SET NOT NULL');
        $this->addSql('ALTER TABLE news_articles ALTER image_url TYPE VARCHAR(500)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE news_articles DROP language');
        $this->addSql('ALTER TABLE news_articles ALTER url TYPE VARCHAR(1000)');
        $this->addSql('ALTER TABLE news_articles ALTER url DROP NOT NULL');
        $this->addSql('ALTER TABLE news_articles ALTER image_url TYPE VARCHAR(1000)');
    }
}
