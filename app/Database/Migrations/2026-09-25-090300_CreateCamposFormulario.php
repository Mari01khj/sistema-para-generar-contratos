<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Catálogo de campos que puede tener un tipo de contrato.
 * Cada fila = un campo (ej. "Fecha de entrega") que el admin definió
 * para cierto tipo_contrato_id. Agregar un campo nuevo = insertar una
 * fila aquí, nunca alterar esta tabla.
 */
class CreateCamposFormulario extends Migration
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
            'tipo_contrato_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'etiqueta' => [
                // Texto que ve el operador en el formulario, ej. "Fecha de entrega"
                'type'       => 'VARCHAR',
                'constraint' => 150,
            ],
            'nombre_campo' => [
                // Nombre técnico usado como marcador en el machote: ${nombre_campo}
                // Sin espacios ni acentos, ej. "fecha_entrega"
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'tipo_dato' => [
                // 'texto' | 'texto_largo' | 'numero' | 'fecha' | 'lista'
                // Guardado como VARCHAR (no ENUM) para poder agregar tipos nuevos
                // sin alterar la tabla; la lista válida se valida en el código PHP.
                'type'       => 'VARCHAR',
                'constraint' => 20,
            ],
            'origen_lista' => [
                // Solo aplica cuando tipo_dato = 'lista'. Dice de qué catálogo
                // salen las opciones: 'proveedores', 'areas', etc.
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'obligatorio' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'orden' => [
                // Posición en la que aparece el campo dentro del formulario
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'activo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => [
                // Borrado lógico: un campo "eliminado" no desaparece,
                // solo deja de mostrarse en contratos NUEVOS. Los contratos
                // que ya lo usaron lo siguen mostrando correctamente.
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['tipo_contrato_id', 'nombre_campo']);
        $this->forge->addForeignKey('tipo_contrato_id', 'tipos_contrato', 'id', 'CASCADE', 'RESTRICT');
        $this->forge->createTable('campos_formulario');
    }

    public function down()
    {
        $this->forge->dropTable('campos_formulario', true);
    }
}
