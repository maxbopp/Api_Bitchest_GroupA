<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240701173043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD wallet_id INT NOT NULL, ADD update_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP updated_at, CHANGE email email VARCHAR(255) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649712520F3 FOREIGN KEY (wallet_id) REFERENCES wallet (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649712520F3 ON user (wallet_id)');
        $this->addSql('DROP INDEX uniq_identifier_username ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USER_NAME ON user (username)');
        $this->addSql('ALTER TABLE wallet DROP FOREIGN KEY FK_7C68921FA76ED395');
        $this->addSql('DROP INDEX IDX_7C68921FA76ED395 ON wallet');
        $this->addSql('ALTER TABLE wallet ADD update_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', DROP user_id, DROP updated_at, CHANGE balance balance INT NOT NULL, CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649712520F3');
        $this->addSql('DROP INDEX UNIQ_8D93D649712520F3 ON user');
        $this->addSql('ALTER TABLE user ADD updated_at DATETIME NOT NULL, DROP wallet_id, DROP update_at, CHANGE email email VARCHAR(100) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL');
        $this->addSql('DROP INDEX uniq_identifier_user_name ON user');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON user (username)');
        $this->addSql('ALTER TABLE wallet ADD user_id INT NOT NULL, ADD updated_at DATETIME NOT NULL, DROP update_at, CHANGE created_at created_at DATETIME NOT NULL, CHANGE balance balance DOUBLE PRECISION NOT NULL');
        $this->addSql('ALTER TABLE wallet ADD CONSTRAINT FK_7C68921FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_7C68921FA76ED395 ON wallet (user_id)');
    }
}
