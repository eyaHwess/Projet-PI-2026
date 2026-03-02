<?php

namespace App\Twig;

use App\Enum\ReclamationStatusEnum;
use App\Repository\ReclamationRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AdminStatsExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private ReclamationRepository $reclamationRepository
    ) {
    }

    public function getGlobals(): array
    {
        $pendingCount = $this->reclamationRepository->count(['status' => ReclamationStatusEnum::PENDING]);
        $totalCount   = $this->reclamationRepository->count([]);

        // Last 5 reclamations for the notification dropdown
        $recentReclamations = $this->reclamationRepository
            ->createQueryBuilder('r')
            ->leftJoin('r.user', 'u')
            ->addSelect('u')
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return [
            'adminPendingReclamations'  => $pendingCount,
            'adminTotalReclamations'    => $totalCount,
            'adminRecentReclamations'   => $recentReclamations,
        ];
    }
}
