<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\ProfileUrlService;
use Illuminate\Console\Command;

class GenerateProfileSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'profiles:generate-sitemap {--output=profiles-sitemap.xml}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap XML for all public profiles';

    /**
     * Execute the console command.
     */
    public function handle(ProfileUrlService $profileUrlService)
    {
        $outputFile = $this->option('output');
        
        $this->info("Generating sitemap for public profiles...");
        
        $publicUsers = User::where('is_public', true)->get();
        
        if ($publicUsers->isEmpty()) {
            $this->warn('No public profiles found.');
            return;
        }
        
        $this->info("Found {$publicUsers->count()} public profiles.");
        
        $xml = $this->generateSitemapXml($publicUsers, $profileUrlService);
        
        file_put_contents($outputFile, $xml);
        
        $this->info("Sitemap generated successfully: {$outputFile}");
        $this->info("Total URLs: " . ($publicUsers->count() * 5)); // 5 tabs per profile
    }
    
    private function generateSitemapXml($users, ProfileUrlService $profileUrlService): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        foreach ($users as $user) {
            $urls = $profileUrlService->getAllProfileUrls($user);
            
            foreach ($urls as $tab => $url) {
                $xml .= '  <url>' . "\n";
                $xml .= '    <loc>' . htmlspecialchars($url) . '</loc>' . "\n";
                $xml .= '    <lastmod>' . $user->updated_at->toISOString() . '</lastmod>' . "\n";
                $xml .= '    <changefreq>weekly</changefreq>' . "\n";
                $xml .= '    <priority>0.8</priority>' . "\n";
                $xml .= '  </url>' . "\n";
            }
        }
        
        $xml .= '</urlset>';
        
        return $xml;
    }
}
