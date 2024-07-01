<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240701171252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE crypto_price (id INT AUTO_INCREMENT NOT NULL, price NUMERIC(10, 0) NOT NULL, date DATE NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', update_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cryptocurrencies (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', update_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', symbol VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE transactions (id INT AUTO_INCREMENT NOT NULL, wallet_id INT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, amount NUMERIC(10, 0) DEFAULT NULL, unit_price NUMERIC(10, 0) DEFAULT NULL, total NUMERIC(10, 0) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', update_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_EAA81A4C712520F3 (wallet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transactions ADD CONSTRAINT FK_EAA81A4C712520F3 FOREIGN KEY (wallet_id) REFERENCES wallet (id)');
        $this->addSql('ALTER TABLE cryptoprice DROP FOREIGN KEY FK_4D326C1D583FC03A');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1712520F3');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1583FC03A');
        $this->addSql('DROP TABLE cryptocurrency');
        $this->addSql('DROP TABLE cryptoprice');
        $this->addSql('DROP TABLE transaction');
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
        $this->addSql('CREATE TABLE cryptocurrency (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, symbol VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE cryptoprice (id INT AUTO_INCREMENT NOT NULL, cryptocurrency_id INT NOT NULL, price DOUBLE PRECISION NOT NULL, date DATETIME NOT NULL, INDEX IDX_4D326C1D583FC03A (cryptocurrency_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, wallet_id INT NOT NULL, cryptocurrency_id INT NOT NULL, type VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, amount DOUBLE PRECISION NOT NULL, price DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_723705D1583FC03A (cryptocurrency_id), INDEX IDX_723705D1712520F3 (wallet_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE cryptoprice ADD CONSTRAINT FK_4D326C1D583FC03A FOREIGN KEY (cryptocurrency_id) REFERENCES cryptocurrency (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1712520F3 FOREIGN KEY (wallet_id) REFERENCES wallet (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1583FC03A FOREIGN KEY (cryptocurrency_id) REFERENCES cryptocurrency (id)');
        $this->addSql('ALTER TABLE transactions DROP FOREIGN KEY FK_EAA81A4C712520F3');
        $this->addSql('DROP TABLE crypto_price');
        $this->addSql('DROP TABLE cryptocurrencies');
        $this->addSql('DROP TABLE transactions');
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
