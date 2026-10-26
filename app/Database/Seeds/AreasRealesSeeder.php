<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;


class AreasRealesSeeder extends Seeder
{
    public function run()
    {
        $areas = [
            ['nombre_area' => 'AUTORIDAD SUBSTANCIADORA Y RESOLUTORA DEL ORGANO INTERNO DE CONTROL MUNICIPAL', 'activo' => 1],
            ['nombre_area' => 'COMISARIA DE SEGURIDAD PUBLICA MUNICIPAL', 'activo' => 1],
            ['nombre_area' => 'COMISARIA DE SEGURIDAD PÚBLICA Y TRANSITO', 'activo' => 1],
            ['nombre_area' => 'CONSEJERIA JURIDICA', 'activo' => 1],
            ['nombre_area' => 'CONTRALORIA INTERNA MUNICIPAL', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE ADQUISICIONES Y SERVICIOS', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE ALUMBRADO PÚBLICO', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE ARCHIVO MUNICIPAL', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE ATENCIÓN A LA JUVENTUD', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE ATENCIÓN A LA SALUD', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE ATENCIÓN A LOS PUEBLOS INDÍGENAS', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE ATENCIÓN A MIGRANTES', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE ATENCIÓN CIUDADANA', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE CALLES,PARQUES,JARDINES,AREAS VERDES Y RECREATIVAS', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE CATASTRO MUNICIPAL', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE COMUNICACIÓN SOCIAL', 'activo' => 1],
            ['nombre_area' => 'COORDINACION DE CONTROL PATRIMONIAL', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE DESARROLLO COMERCIAL', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE ECOLOGIA', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE EDUCACIÓN', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE FOMENTO ARTESANAL', 'activo' => 1],
            ['nombre_area' => 'COORDINACION DE LIMPIA Y RESIDUOS', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE LIMPIA, RECOLECCIÓN Y DISPOSICIÓN DE RESIDUOS', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE MERCADOS', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE PANTEONES, PARQUES Y JARDINES', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE PARQUE VEHICULAR Y MAQUINARIA', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE PREVENCIÓN DEL DELITO', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE RASTRO MUNICIPAL', 'activo' => 1],
            ['nombre_area' => 'COORDINACION DE RECURSOS HUMANOS', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE RECURSOS MATERIALES', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN DE VIALIDAD Y TRANSPORTE', 'activo' => 1],
            ['nombre_area' => 'COORDINACION DE VINCULACION CIUDADANA', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN GENERAL DE MEJORA REGULATORIA', 'activo' => 1],
            ['nombre_area' => 'COORDINACIÓN MUNICIPAL DE PROTECCIÓN CIVIL Y BOMBEROS', 'activo' => 1],
            ['nombre_area' => 'CORDINACION DEL RASTRO MUNICIPAL', 'activo' => 1],
            ['nombre_area' => 'CUARTA REGIDURIA', 'activo' => 1],
            ['nombre_area' => 'DEFENSORIA MUNICIPAL DE DERECHOS HUMANOS', 'activo' => 1],
            ['nombre_area' => 'DIRECCIÓN DE ADMINISTRACIÓN Y DESARROLLO DE PERSONAL', 'activo' => 1],
            ['nombre_area' => 'DIRECCIÓN DE ATENCIÓN A LA MUJER', 'activo' => 1],
            ['nombre_area' => 'DIRECCIÓN DE CULTURA Y TURISMO', 'activo' => 1],
            ['nombre_area' => 'DIRECCIÓN DE DESARROLLO AGROPECUARIO', 'activo' => 1],
            ['nombre_area' => 'DIRECCIÓN DE DESARROLLO ECONÓMICO', 'activo' => 1],
            ['nombre_area' => 'DIRECCIÓN DE DESARROLLO SOCIAL', 'activo' => 1],
            ['nombre_area' => 'DIRECCIÓN DE DESARROLLO URBANO', 'activo' => 1],
            ['nombre_area' => 'DIRECCIÓN DE GOBERNACIÓN', 'activo' => 1],
            ['nombre_area' => 'DIRECCIÓN DE LAS MUJERES', 'activo' => 1],
            ['nombre_area' => 'DIRECCIÓN DE OBRAS PÚBLICAS', 'activo' => 1],
            ['nombre_area' => 'DIRECCIÓN DE SERVICIOS PÚBLICOS', 'activo' => 1],
            ['nombre_area' => 'DIRECCION MUNICIPAL DE SIMPLIFICACION Y DIGITALIZACION', 'activo' => 1],
            ['nombre_area' => 'JUZGADO CIVICO', 'activo' => 1],
            ['nombre_area' => 'OFICIALIA 02 DEL REGISTRO CIVIL', 'activo' => 1],
            ['nombre_area' => 'OFICIALIA 03 DEL REGISTRO CIVIL', 'activo' => 1],
            ['nombre_area' => 'OFICIALIA DEL REGISTRO CIVIL 01', 'activo' => 1],
            ['nombre_area' => 'ORGANO INTERNO DE CONTROL MUNICIPAL', 'activo' => 1],
            ['nombre_area' => 'PRESIDENCIA MUNICIPAL', 'activo' => 1],
            ['nombre_area' => 'PRIMER REGIDURIA', 'activo' => 1],
            ['nombre_area' => 'QUINTA REGIDURIA', 'activo' => 1],
            ['nombre_area' => 'REGISTRO CIVIL 03', 'activo' => 1],
            ['nombre_area' => 'SECRETARIA DEL AYUNTAMIENTO', 'activo' => 1],
            ['nombre_area' => 'SECRETARIA PARTICULAR', 'activo' => 1],
            ['nombre_area' => 'SECRETARIA TECNICA', 'activo' => 1],
            ['nombre_area' => 'SECRETARIA TECNICA DE SEGURIDAD PUBLICA', 'activo' => 1],
            ['nombre_area' => 'SEGUNDA REGIDURIA', 'activo' => 1],
            ['nombre_area' => 'SÉPTIMA REGIDURIA', 'activo' => 1],
            ['nombre_area' => 'SÉPTIMA REGUDURIA', 'activo' => 1],
            ['nombre_area' => 'SEXTA REGIDURIA', 'activo' => 1],
            ['nombre_area' => 'SINDICATURA', 'activo' => 1],
            ['nombre_area' => 'TERCER REGIDURIA', 'activo' => 1],
            ['nombre_area' => 'TESORERIA MUNICIPAL', 'activo' => 1],
            ['nombre_area' => 'UNIDAD DE CONTROL Y BIENESTAR ANIMAL', 'activo' => 1],
            ['nombre_area' => 'UNIDAD DE INFORMACIÓN, PLANEACIÓN, PROGRAMACIÓN Y EVALUACIÓN', 'activo' => 1],
            ['nombre_area' => 'UNIDAD DE TRANSPARENCIA Y ACCESO A LA INFORMACIÓN', 'activo' => 1],
            ['nombre_area' => 'VARIAS DEPENDENCIAS', 'activo' => 1],        ];

        foreach ($areas as $area) {
            $existe = $this->db->table('areas')
                ->where('nombre_area', $area['nombre_area'])
                ->countAllResults();

            if ($existe === 0) {
                $this->db->table('areas')->insert(array_merge($area, [
                    'titular_area'  => '',
                    'cargo_titular' => '',
                    'created_at'    => date('Y-m-d H:i:s'),
                ]));
            }
        }
    }
}
