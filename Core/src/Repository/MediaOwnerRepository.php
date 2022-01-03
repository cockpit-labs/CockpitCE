<?php
/*
 * Core
 * MediaOwnerRepository.php
 *
 * Copyright (c) 2020 Sentinelo
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

use App\Entity\Media\MediaOwner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MediaOwner|null find($id, $lockMode = null, $lockVersion = null)
 * @method MediaOwner|null findOneBy(array $criteria, array $orderBy = null)
 * @method MediaOwner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MediaOwnerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaOwner::class);
    }

    /**
     * @return bool Returns true if object exists in db
     */
    public function exists(MediaOwner $mediaOwner)
    {
        $nb    = 0;
        $media = $mediaOwner->getMedia();
        if (!empty($mediaOwner) && get_class($mediaOwner) == MediaOwner::class) {

            $owner  = $mediaOwner->getOwner();
            $result = $this->createQueryBuilder('m')
                           ->andWhere('m.owner = :owner')
                           ->setParameter('owner', $owner)
                           ->andWhere('m.media = :media')
                           ->setParameter('media', $media->getId())
                           ->getQuery()
                           ->getResult();
            $nb     = count($result);
        }
        return ($nb > 0);
    }

    public function findAll()
    {
        return [];
    }

}
