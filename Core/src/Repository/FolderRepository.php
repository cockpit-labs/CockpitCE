<?php
/*
 * Core
 * FolderRepository.php
 *
 * Copyright (c) 2021 Sentinelo
 *
 * @author  Christophe AGNOLA
 * @license MIT License (https://mit-license.org)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the “Software”), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies
 * or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT
 * NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */

namespace App\Repository;

use App\Entity\Folder\Folder;
use App\Entity\Folder\FolderTpl;
use App\Entity\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Folder|null find($id, $lockMode = null, $lockVersion = null)
 * @method Folder|null findOneBy(array $criteria, array $orderBy = null)
 * @method Folder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FolderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Folder::class);
    }

    public function perCurrentPeriodCount(
        FolderTpl $folderTpl,
        Group     $groupTarget,
        string    $author = '',
        bool      $now = true
    ) {
        $periodEnd = $periodStart = null;
        foreach ($folderTpl->getCalendars() as $calendar) {

            $periodEnd = max($calendar->getPeriodEnd(), $periodEnd);
            // periodStart cannot be null, so force it to first end value, if it is null
            $periodStart = $periodStart ?? $periodEnd;
            $periodStart = min($calendar->getPeriodStart(), $periodStart);
        }

        $query = $this->createQueryBuilder('folder')
                      ->distinct(true)
                      ->andWhere(sprintf("folder.appliedTo = '%s'", $groupTarget->getId()))
                      ->andWhere(sprintf("folder.folderTpl = '%s'", $folderTpl->getId()))
                      ->andWhere("folder.periodStart = :foldertplPeriodStart")
                      ->setParameter('foldertplPeriodStart', \DateTimeImmutable::createFromMutable($periodStart),
                                     Types::DATETIME_IMMUTABLE)
                      ->andWhere("folder.periodEnd = :foldertplPeriodEnd")
                      ->setParameter('foldertplPeriodEnd', \DateTimeImmutable::createFromMutable($periodEnd),
                                     Types::DATETIME_IMMUTABLE)
                      ->andWhere("folder.deletedAt IS NULL");

        $result = $query->getQuery()->getResult();

        return count($result);
    }

}
