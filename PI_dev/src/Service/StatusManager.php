<?php

namespace App\Service;

use App\Entity\Goal;
use App\Entity\Routine;
use App\Entity\Activity;
use Doctrine\ORM\EntityManagerInterface;

class StatusManager
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Met à jour automatiquement les statuts d'un goal et ses routines
     */
    public function updateGoalStatuses(Goal $goal): void
    {
        // Mettre à jour les statuts des routines
        foreach ($goal->getRoutines() as $routine) {
            $routine->updateAutoStatus();
        }

        // Mettre à jour le statut du goal
        $goal->updateAutoStatus();

        $this->entityManager->flush();
    }

    /**
     * Met à jour automatiquement les statuts d'une routine
     */
    public function updateRoutineStatuses(Routine $routine): void
    {
        $routine->updateAutoStatus();
        
        // Mettre à jour aussi le goal parent
        if ($routine->getGoal()) {
            $routine->getGoal()->updateAutoStatus();
        }

        $this->entityManager->flush();
    }

    /**
     * Met à jour les statuts après modification d'une activité
     */
    public function updateActivityStatuses(Activity $activity): void
    {
        $routine = $activity->getRoutine();
        
        if ($routine) {
            $routine->updateAutoStatus();
            
            $goal = $routine->getGoal();
            if ($goal) {
                $goal->updateAutoStatus();
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Vérifie si une activité peut être exécutée
     */
    public function canExecuteActivity(Activity $activity): bool
    {
        $routine = $activity->getRoutine();
        
        if (!$routine) {
            return false;
        }

        // L'activité ne peut être exécutée que si la routine peut être exécutée
        return $routine->canBeExecuted();
    }

    /**
     * Bloque toutes les activités d'un goal en pause
     */
    public function blockActivitiesForPausedGoal(Goal $goal): void
    {
        if ($goal->getStatus() !== 'paused') {
            return;
        }

        foreach ($goal->getRoutines() as $routine) {
            foreach ($routine->getActivities() as $activity) {
                // Mettre en pause les activités en cours ou en attente
                if (in_array($activity->getStatus(), ['pending', 'in_progress'])) {
                    $activity->setStatus('pending'); // Remettre en attente
                }
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Vérifie tous les goals pour mettre à jour les statuts automatiquement
     */
    public function checkAllGoalsStatuses(): void
    {
        $goalRepository = $this->entityManager->getRepository(Goal::class);
        $goals = $goalRepository->findAll();

        foreach ($goals as $goal) {
            $this->updateGoalStatuses($goal);
        }
    }
}
