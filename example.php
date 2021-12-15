<?php

namespace Notification\Service\EventHandler\ElectronicQueue;

use App\Entity\Owner\Owner;
use App\Entity\Dictionary\Crops;
use App\Entity\Place\Place;
use App\Model\Constant\PlaceType;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Notification\Entity\Event\Event;
use Notification\Model\Constant\NotificationEventType;
use Notification\Service\EventHandler\AbstractNotificationEventHandler;

/**
 * Class GroupPassedEventHandler.
 *
 * Пример создания нотификаций
 */
class GroupPassedEventHandler extends AbstractNotificationEventHandler implements RequiredNotificationDataHolderInterface
{
    use ElectronicQueueEventHandlerTrait;

    /** @var Place|null */
    private $place;

    /** @var string */
    private $date;

    /** @var Owner|null */
    private $creator;

    /** @var int */
    private $count;

    /** @var array */
    private $crops;

    /** @var mixed */
    private $originIds;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function processEvent(Event $event): void
    {
        $this->data = $event->getData();
        $this->place = $this->entityManager->getRepository(Place::class)->findOneBy([
            'id' => $this->data['notificationData']['placeId'],
            'type' => PlaceType::PLACE,
        ]);
        $this->date = $this->data['notificationData']['date'];
        $this->creator = $this->entityManager->getRepository(Owner::class)->findOneBy([
            'id' => $this->data['notificationData']['creater'],
        ]);
        $this->count = $this->data['notificationData']['count'];
        $this->crops = $this->data['notificationData']['crops'];
        $this->originIds = $this->data['notificationData']['originId'];
    }

    /**
     * {@inheritdoc}
     */
    public function getEventType(): string
    {
        return NotificationEventType::GROUP_PASSED;
    }

    /**
     * @return string
     */
    public function getPlaceTitle(): string
    {
        return $this->place ? $this->place->getTitle() : 'Place not found';
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->data['notificationData']['date'];
    }

    /**
     * @return string
     */
    public function getCreator(): string
    {
        return $this->creator ? $this->creator->getProfileDetails()->getOrganizationName() : '';
    }

    /**
     * @return int
     */
    public function getcount(): int
    {
        return $this->count;
    }

    /**
     * @return array
     */
    public function getCrops(): array
    {
        $cropsRepository = $this->entityManager->getRepository(Crops::class);
        $cropNames = [];

        foreach ($this->crops as $cropId) {
            if ($crop = $cropsRepository->findOneBy(['id' => $cropId])) {
                $cropNames[] = $crop->getName();
            }
        }

        return $cropNames;
    }

    /**
     * @return string
     */
    public function getOrigins(): string
    {
        $placeRepository = $this->entityManager->getRepository(Place::class);
        $originsNames = [];
        if (!is_array($this->originIds)) {
            $this->originIds = [$this->originIds];
        }

        foreach ($this->originIds as $originId) {
            if ($origin = $placeRepository->findOneBy(['id' => $originId])) {
                $originsNames[] = $origin->getTitle();
            }
        }
        switch (count($originsNames)) {
            case 0:
                return '';
            case 1:
                return ', place ' . implode($originsNames);
            default:
                return ', place ' . implode(', ', $originsNames);
        }
    }

    /**
     * @return array
     */
    public function getRequiredData(): array
    {
        return [
            'placeId',
            'date',
            'creater',
            'count',
            'crops',
            'originId',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getReceivers(): array
    {
        $owners = [];
        $managers = [];
        foreach ($this->data['receivers'] as $ownerId) {
            $owner = $this->entityManager->getRepository(Owner::class)->find($ownerId);
            $owners[] = $owner;
            $ownerManagers = $owner->getManagers();
            if ($ownerManagers instanceof Collection) {
                $ownerManagers = $ownerManagers->toArray();
            }
            $managers = array_merge($managers, $ownerManagers);
        }

        return array_merge($owners, $managers);
    }

    /**
     * {@inheritdoc}
     */
    protected function getPlaceHoldersCallables(): array
    {
        return [
            'placeTitle' => [$this, 'getPlaceTitle'],
            'date' => [$this, 'getDate'],
            'creator' => [$this, 'getCreator'],
            'count' => [$this, 'getCount'],
            'crops' => [$this, 'getCrops'],
            'origins' => [$this, 'getOrigins'],
        ];
    }
}
