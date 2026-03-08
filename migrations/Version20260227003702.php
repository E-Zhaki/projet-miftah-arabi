<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260227003702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lesson_tag (lesson_id INT NOT NULL, tag_id INT NOT NULL, INDEX IDX_71FC1160CDF80196 (lesson_id), INDEX IDX_71FC1160BAD26311 (tag_id), PRIMARY KEY (lesson_id, tag_id)) DEFAULT CHARACTER SET utf8mb4 ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lesson_tag ADD CONSTRAINT FK_71FC1160CDF80196 FOREIGN KEY (lesson_id) REFERENCES lesson (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE lesson_tag ADD CONSTRAINT FK_71FC1160BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lesson_tag DROP FOREIGN KEY FK_71FC1160CDF80196');
        $this->addSql('ALTER TABLE lesson_tag DROP FOREIGN KEY FK_71FC1160BAD26311');
        $this->addSql('DROP TABLE lesson_tag');
    }
}
