<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250501133158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            DROP SEQUENCE vol_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE questions_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            DROP SEQUENCE choices_id_seq CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.created_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.available_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            COMMENT ON COLUMN messenger_messages.delivered_at IS '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
                BEGIN
                    PERFORM pg_notify('messenger_messages', NEW.queue_name::text);
                    RETURN NEW;
                END;
            $$ LANGUAGE plpgsql;
        SQL);
        $this->addSql(<<<'SQL'
            DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE choices DROP CONSTRAINT choices_question_id_fkey
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE choices
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE vol
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE aymen_schema.app_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE cars
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE "user"
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE app_users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE questions
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SCHEMA aymen_schema
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SCHEMA product
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE vol_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE questions_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE SEQUENCE choices_id_seq INCREMENT BY 1 MINVALUE 1 START 1
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE choices (id SERIAL NOT NULL, question_id INT DEFAULT NULL, choice_text VARCHAR(255) DEFAULT NULL, is_correct BOOLEAN DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX ix_choices_id ON choices (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX ix_choices_choice_text ON choices (choice_text)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5CE96391E27F6BF ON choices (question_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE vol (id SERIAL NOT NULL, numero_vol VARCHAR(20) NOT NULL, ville_depart VARCHAR(50) NOT NULL, ville_arrivee VARCHAR(50) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX vol_numero_vol_key ON vol (numero_vol)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE aymen_schema.app_users (id BIGINT NOT NULL, email VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uk4vj92ux8a2eehds1mdvmks473 ON aymen_schema.app_users (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE cars (id BIGINT NOT NULL, datetk TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, is_verified BOOLEAN NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uniq_identifier_email ON "user" (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE app_users (id BIGINT NOT NULL, email VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX uk4vj92ux8a2eehds1mdvmks473 ON app_users (email)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE questions (id SERIAL NOT NULL, question_text VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX ix_questions_id ON questions (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX ix_questions_question_text ON questions (question_text)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE choices ADD CONSTRAINT choices_question_id_fkey FOREIGN KEY (question_id) REFERENCES questions (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
