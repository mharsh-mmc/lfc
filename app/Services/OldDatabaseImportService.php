<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Exception;

class OldDatabaseImportService
{
    /**
     * Import old database structure
     */
    public function importOldDatabaseStructure()
    {
        try {
            Log::info("Starting old database structure import");
            
            // Create old database tables
            $this->createOldTables();
            
            // Import data from SQL file
            $this->importDataFromSqlFile();
            
            Log::info("Successfully imported old database structure");
            return true;
            
        } catch (Exception $e) {
            Log::error("Failed to import old database structure: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Create old database tables
     */
    private function createOldTables()
    {
        // Create anagrafica table
        if (!Schema::hasTable('anagrafica')) {
            Schema::create('anagrafica', function ($table) {
                $table->bigInteger('id')->primary();
                $table->string('nome', 50)->default('0');
                $table->string('detta', 50)->default('');
                $table->string('cognome', 50)->default('0');
                $table->tinyInteger('sesso')->default(0);
                $table->date('datanascita')->default('1828-01-01');
                $table->date('datamorte')->nullable();
                $table->string('cittanascita', 50)->default('0');
                $table->string('provincianascita', 50)->default('0');
                $table->string('nazionenascita', 50)->default('0');
                $table->string('mail', 50)->default('0');
                $table->string('tel', 50)->default('0');
                $table->datetime('datainserimento')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->datetime('dataultimamodifica')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'));
                $table->bigInteger('idinseritore')->default(0);
                $table->bigInteger('immagine')->default(0);
                $table->string('cittaresidenza', 50)->default('');
                $table->string('provinciaresidenza', 50)->default('');
                $table->string('nazioneresidenza', 50)->default('');
                $table->string('linkfb', 50)->default('');
                $table->string('linktweter', 50)->default('');
                $table->string('linkyoutube', 50)->default('');
                $table->integer('curriculum')->default(0);
                $table->integer('foto')->default(0);
                $table->integer('lifeextention')->default(0);
                $table->integer('crediti')->default(0);
                $table->integer('occupazione')->default(0);
                $table->string('descrizioneoccupazione', 100)->nullable();
                $table->string('cf', 20)->default('');
                $table->string('titolodistudio', 100)->default('');
                $table->string('causa_decesso', 50)->nullable();
                $table->integer('numeroaborti')->default(0);
            });
        }
        
        // Create genealogical_tree table
        if (!Schema::hasTable('genealogical_tree')) {
            Schema::create('genealogical_tree', function ($table) {
                $table->integer('id')->primary();
                $table->bigInteger('pid');
                $table->integer('gttid');
                $table->datetime('payed_at')->nullable();
                $table->smallInteger('flag_active')->default(0);
                $table->string('stripe_payment_intent_id', 255)->nullable();
                $table->decimal('total', 11, 2)->nullable();
            });
        }
        
        // Create genealogical_tree_person table
        if (!Schema::hasTable('genealogical_tree_person')) {
            Schema::create('genealogical_tree_person', function ($table) {
                $table->integer('id')->primary();
                $table->integer('gtid');
                $table->bigInteger('pid');
                $table->integer('trid');
                $table->string('position', 10)->default('n/a');
                $table->integer('traid');
            });
        }
        
        // Create genealogical_tree_template table
        if (!Schema::hasTable('genealogical_tree_template')) {
            Schema::create('genealogical_tree_template', function ($table) {
                $table->integer('id')->primary();
                $table->string('title', 70);
                $table->text('description')->nullable();
                $table->string('picture', 70)->nullable();
                $table->string('template', 100)->nullable();
                $table->decimal('cost', 11, 2)->nullable();
            });
        }
        
        // Create genealogical_tree_template_item table
        if (!Schema::hasTable('genealogical_tree_template_item')) {
            Schema::create('genealogical_tree_template_item', function ($table) {
                $table->integer('id')->primary();
                $table->integer('gttid');
                $table->smallInteger('max_persons');
                $table->string('position', 10)->default('n/a');
                $table->smallInteger('priority')->default(0);
                $table->integer('traid');
            });
        }
        
        // Create citta table
        if (!Schema::hasTable('citta')) {
            Schema::create('citta', function ($table) {
                $table->integer('id')->primary();
                $table->string('cod_istat', 50);
                $table->string('nome', 50)->nullable();
                $table->char('provincia_id', 2);
            });
        }
    }
    
    /**
     * Import data from SQL file
     */
    private function importDataFromSqlFile()
    {
        $sqlFile = storage_path('app/Sql1706700_3.sql');
        
        if (!file_exists($sqlFile)) {
            throw new Exception("SQL file not found at: {$sqlFile}");
        }
        
        // Read SQL file content
        $sqlContent = file_get_contents($sqlFile);
        
        // Split into individual statements
        $statements = $this->splitSqlStatements($sqlContent);
        
        // Execute each statement
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement) && !$this->isComment($statement)) {
                try {
                    DB::unprepared($statement);
                } catch (Exception $e) {
                    Log::warning("Failed to execute SQL statement: " . substr($statement, 0, 100) . "... Error: " . $e->getMessage());
                    // Continue with other statements
                }
            }
        }
    }
    
    /**
     * Split SQL content into individual statements
     */
    private function splitSqlStatements($sqlContent)
    {
        // Remove comments
        $sqlContent = preg_replace('/--.*$/m', '', $sqlContent);
        $sqlContent = preg_replace('/\/\*.*?\*\//s', '', $sqlContent);
        
        // Split by semicolon
        $statements = explode(';', $sqlContent);
        
        return array_filter(array_map('trim', $statements));
    }
    
    /**
     * Check if line is a comment
     */
    private function isComment($line)
    {
        $line = trim($line);
        return empty($line) || 
               str_starts_with($line, '--') || 
               str_starts_with($line, '/*') || 
               str_starts_with($line, '*/');
    }
    
    /**
     * Check if old database is imported
     */
    public function isOldDatabaseImported()
    {
        return Schema::hasTable('anagrafica') && 
               Schema::hasTable('genealogical_tree') &&
               DB::table('anagrafica')->count() > 0;
    }
    
    /**
     * Get old database statistics
     */
    public function getOldDatabaseStats()
    {
        if (!$this->isOldDatabaseImported()) {
            return null;
        }
        
        return [
            'total_people' => DB::table('anagrafica')->count(),
            'total_trees' => DB::table('genealogical_tree')->where('flag_active', 1)->count(),
            'total_tree_people' => DB::table('genealogical_tree_person')->count(),
            'total_templates' => DB::table('genealogical_tree_template')->count(),
            'total_cities' => DB::table('citta')->count(),
        ];
    }
}