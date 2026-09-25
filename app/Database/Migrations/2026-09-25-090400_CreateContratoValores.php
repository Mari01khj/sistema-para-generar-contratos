<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Una fila por cada campo lleno de cada contrato.
 * Ej: (contrato_id=57, campo_id=3, valor="2026-12-12")
 * Así, agregar un campo nuevo a un tipo de contrato nunca requiere
 * tocar esta tabla ni la de contratos: solo se agregan más filas.
 */
class CreateContratoValores extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'contrato_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'campo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'valor' => [
                // TEXT y no VARCHAR: algunos campos serán "texto_largo"
                // y no sabemos de antemano qué tan largo será cada valor.
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addKey('id', true);
        // Un mismo campo no puede tener dos valores para el mismo contrato.
        $this->forge->addUniqueKey(['contrato_id', 'campo_id']);

        // Si se borra el contrato (hard delete), sus valores se van con él.
        $this->forge->addForeignKey('contrato_id', 'contratos', 'id', 'CASCADE', 'CASCADE');
        // No se puede borrar un campo si todavía tiene valores guardados
        // (por eso campos_formulario usa borrado lógico, no borrado real).
        $this->forge->addForeignKey('campo_id', 'campos_formulario', 'id', 'RESTRICT', 'RESTRICT');

        $this->forge->createTable('contrato_valores');
    }

    public function down()
    {
        $this->forge->dropTable('contrato_valores', true);
    }
}
