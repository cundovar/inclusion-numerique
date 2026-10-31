<?php
declare(strict_types=1);
namespace App\Command;
use App\Service\Rag\RagIndex;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
#[AsCommand(name: 'app:rag:reindex', description: 'Réindexe les cours publiés dans le moteur vectoriel privé.')]
final class ReindexVectorSearchCommand extends Command { public function __construct(private RagIndex $index){parent::__construct();} protected function execute(InputInterface $input, OutputInterface $output): int { $result=$this->index->rebuildVectors(); $output->writeln(sprintf('%d cours vectorisés.', $result['indexed'])); if ($result['failed'] !== []) { $output->writeln(sprintf('<error>Échec pour les cours %s : vérifier RAG_VECTOR_URL, RAG_VECTOR_TOKEN et les journaux du service vectoriel.</error>', implode(', ', $result['failed']))); return Command::FAILURE; } return Command::SUCCESS; } }
