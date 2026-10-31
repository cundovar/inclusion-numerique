<?php
declare(strict_types=1);
namespace App\Service\Learner;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
/** Lit les définitions locales des laboratoires sans exécuter leur workflow. */
final class LaboratoryCatalogue {
 public function __construct(#[Autowire('%kernel.project_dir%')] private string $projectDir) {}
 /** @return list<array<string,mixed>> */ public function list(?string $niveau=null): array { $labs=[]; foreach(glob($this->projectDir.'/front/src/laboratoires/catalogue/*.json') ?: [] as $file){$d=json_decode((string) file_get_contents($file),true); if(!is_array($d)||($niveau!==null&&($d['niveau']??null)!==$niveau))continue; $labs[]=$this->format($d);} usort($labs,static fn($a,$b)=>[$a['niveau'],$a['titre']]<=>[$b['niveau'],$b['titre']]); return $labs; }
 /** @return array<string,mixed>|null */ public function find(string $slug): ?array { foreach($this->list() as $lab)if($lab['slug']===$slug)return $lab; return null; }
 /** @param array<string,mixed> $d @return array<string,mixed> */ private function format(array $d): array{return ['slug'=>$d['slug'],'titre'=>$d['titre'],'niveau'=>$d['niveau'],'duree'=>$d['duree']??null,'objectif'=>$d['objectif']??null,'instructions'=>$d['instructions']??[],'simulation'=>$d['simulation']??null,'adresse'=>'/spa/laboratoires/'.$d['slug']];}
}
