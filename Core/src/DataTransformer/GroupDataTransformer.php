<?php
/*
 * Core
 * GroupDataTransformer.php
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

namespace App\DataTransformer;

use ApiPlatform\Core\DataTransformer\DataTransformerInterface;
use App\DataProvider\CommonDataProvider;
use App\Entity\Folder\Folder;
use App\Entity\Group;
use Doctrine\DBAL\Types\Types;

final class GroupDataTransformer extends CommonDataProvider implements DataTransformerInterface
{

    /**
     * @var array
     */
    private array $context;

    private function get(Group &$group)
    {
        $targets  = $group->getTargets();
        $group->removeTarget();
        $places=[];
        $initialPlaces=[];

        foreach ($targets as $target) {
            if ($target->getOwnerId() == $this->getUserId()) {
                $folderQb = $this->getEntityManager()->createQueryBuilder()
                                 ->select('folder')
                                 ->from(Folder::class, 'folder');
                $folderTpl    = $target->getFolderTpl();

                $targetTransition=$target->getRight()->getId();
                if(!empty($transistions[$targetTransition])){
                    $targetPlaces = [];
                    foreach ($transistions[$targetTransition] as $t) {
                        $targetPlaces = array_merge($targetPlaces, array_values($t->getFroms()));
                    }

                    $folderQb->where(sprintf("folder.folderTpl = '%s'", $folderTpl->getId()))
                             ->andWhere("folder.deletedAt is null");
                    if (array_intersect($targetPlaces, $initialPlaces)) {
                        // first place/state
                        // so: check dates
                        $folderQb->andWhere("folder.periodEnd >= :before")
                                 ->setParameter('before', new \DateTimeImmutable(), Types::DATETIME_IMMUTABLE);
                    }
                    $sql     = $folderQb->getQuery()->getSQL();
                    $folders = $folderQb->getQuery()->getResult();

                    foreach ($folders as $folder) {
                                $target->addFolder($folder);
                    }
                }
                // if CREATE: check if we are not off limit
                $inProgressFoldersCount = $this->getEntityManager()
                                               ->getRepository(Folder::class)
                                               ->perCurrentPeriodCount($folderTpl, $group);
                $maxFolders=$folderTpl->getMaxFolders();
                if ($target->getRight()->getId() !== 'CREATE' || $inProgressFoldersCount < $maxFolders) {
                    $group->addTarget($target);
                }
            }
        }
    }

    /**
     * @param array|object $data
     * @param string       $to
     * @param array        $context
     *
     * @return bool
     */
    public function supportsTransformation($data, string $to, array $context = []): bool
    {
        // transform only Folder creation,update and get
        if (Group::class !== $to) {
            return false;
        }

        if (in_array($context[$context['operation_type'] . '_operation_name'], ['get']) && is_a($data, Group::class)) {
            return true;
        }
        return false;
    }

    /**
     * @param object $data
     * @param string $to
     * @param array  $context
     *
     */
    public function transform($data, string $to, array $context = [])
    {
        $this->context = $context;

        switch ($context[$context['operation_type'] . '_operation_name']) {
            case 'get':
                $this->get($data);
                break;

            default:
                // what?
                // an unknown operation?
                break;
        }
        return $data;
    }
}
