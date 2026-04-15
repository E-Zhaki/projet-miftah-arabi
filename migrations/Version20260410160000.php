<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260410160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a unique constraint on favorite_list (user_id, lesson_id)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DELETE f1 FROM favorite_list f1 INNER JOIN favorite_list f2 WHERE f1.id > f2.id AND f1.user_id = f2.user_id AND f1.lesson_id = f2.lesson_id');
        $this->addSql('CREATE UNIQUE INDEX uniq_favorite_user_lesson ON favorite_list (user_id, lesson_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX uniq_favorite_user_lesson ON favorite_list');
    }
}
