<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:doctrine:report-n-plus-one',
    description: 'Aide pour mesurer les problèmes N+1 (Doctrine) et remplir le tableau d\'optimisation',
)]
class DoctrineReportNPlusOneCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Doctrine – Détection des problèmes N+1 (Doctrine Doctor)');

        $io->section('Comment mesurer (Profiler Symfony)');
        $io->listing([
            'Lancer l\'app en dev (ex: symfony server:start ou php -S localhost:8000 -t public).',
            'Ouvrir les pages listées ci-dessous dans le navigateur.',
            'Cliquer sur la barre de debug en bas de page → onglet "Doctrine".',
            'Noter le <comment>nombre de requêtes</comment> pour chaque page.',
            'Si vous voyez 1 requête + N requêtes similaires (une par élément), c\'est un N+1.',
            'Faire une capture d\'écran du panneau Doctrine pour les preuves (avant / après).',
        ]);

        $io->section('Pages à tester (scénarios susceptibles de N+1)');
        $io->table(
            ['Page', 'URL typique', 'Risque N+1'],
            [
                ['Liste des goals', '/goals', 'getGoalParticipations() ou getChatroom() par goal'],
                ['Page communauté (goals)', '/goals/community', 'Participations / utilisateurs par goal'],
                ['Liste des posts', '/posts', 'Comments / createdBy par post'],
                ['Détail d\'un goal', '/goals/{id}', 'Participations, routines, activités'],
                ['Chatroom / messages', '/message/chatroom/{goalId}', 'Auteurs ou réactions par message'],
            ]
        );

        $io->section('Remplir le tableau du rapport');
        $io->text([
            '• <info>Avant optimisation</info> : noter le nombre de requêtes et le nombre de problèmes N+1 (ex: 1 problème = liste goals sans jointure).',
            '• <info>Après optimisation</info> : après avoir ajouté des jointures (JOIN + addSelect), noter le nouveau nombre de requêtes.',
            '• <info>Preuves</info> : captures d\'écran du panneau Doctrine (liste des requêtes) avant et après.',
            '• Guide détaillé : <comment>docs/doctrine-doctor-optimisation.md</comment>',
        ]);

        $io->success('Procédure rappelée. Utilisez le Profiler Symfony (onglet Doctrine) pour les mesures.');

        return Command::SUCCESS;
    }
}
