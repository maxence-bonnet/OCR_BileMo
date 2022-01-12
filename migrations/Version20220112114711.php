<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220112114711 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE client_phone (client_id INT NOT NULL, phone_id INT NOT NULL, INDEX IDX_E7220B6A19EB6921 (client_id), INDEX IDX_E7220B6A3B7323CB (phone_id), PRIMARY KEY(client_id, phone_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client_phone ADD CONSTRAINT FK_E7220B6A19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE client_phone ADD CONSTRAINT FK_E7220B6A3B7323CB FOREIGN KEY (phone_id) REFERENCES phone (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE client_phone');
    }
}
