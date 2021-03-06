<?php

namespace App\Service;

use App\Entity\User;
use App\Helper\Message;
use App\Helper\Random;
use App\Helper\Time;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class Crime
 * @author Edwin ten Brinke <edwin.ten.brinke@extendas.com>
 */
class CrimeService
{
    public const CRIME_JACKPOT_CHANCE = 2.5;

    private $translator;
    private $rank;
    private $car;

    public function __construct(RankService $rank, CarService $car, TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->rank = $rank;
        $this->car = $car;
    }

    /**
     * @param User $user
     *
     * @throws \Exception
     */
    public function executeGta(User $user)
    {
        $rank = $this->rank->getUserRank($user);

        // check cooldown
        Time::isFuture($user->getCooldown()->getGrandTheftAuto());

        $car = null;
        $amount = null;
        $chance = Random::chance();
        switch(true) {
            case $chance < $rank->gta_chance:
                list($message, $car) = $this->car->getCar($user, $rank);

                $user->addExperience(50);
                $user->getCounter()->addGrandTheftAuto();
                break;
            default:
                //failed
                $message = Message::failure();
        }

        $user->getCooldown()->setGrandTheftAuto(Time::addSeconds(2));

        return [$message, $car];
    }

    /**
     * @param User $user
     *
     * @return int
     * @throws \Exception
     */
    public function executeCrime(User $user)
    {
        $rank = $this->rank->getUserRank($user);

        // TODO Prison check
        // RANK: Empty suit

        // check cooldown
        Time::isFuture($user->getCooldown()->getCrime());

        $amount = null;
        $chance = Random::chance();
        switch(true) {
            case $chance < self::CRIME_JACKPOT_CHANCE:
                $amount = Random::between(1000, 2500);
                $message = Message::jackpot($amount);
                break;
            case $chance < $rank->crime_chance:
                $amount = Random::between(100, 250);
                $message = Message::success($amount);
                break;
            default:
                //failed
                $message = Message::failure();
        }

        if ($amount)
        {
            $user->addCash($amount);
            $user->addExperience(10);
            $user->getCounter()->addCrime();
        }

        // set cooldown
        $user->getCooldown()->setCrime(Time::addSeconds(2));

        return $message;
    }

    /**
     * @param User $user
     *
     * @return int
     * @throws \Exception
     */
    public function executeOrganizedCrime(User $user)
    {
        $rank = $this->rank->getUserRank($user);

        //TODO prison
        RankService::isAllowed($user, RankService::RANKS['Deliveryboy']);
        Time::isFuture($user->getCooldown()->getOrganizedCrime());

        $amount = null;
        $chance = Random::chance();
        switch(true) {
            case $chance < self::CRIME_JACKPOT_CHANCE:
                $amount = (Random::between(750, 1500) * 5);
                $message = Message::jackpot($amount);
                break;
            case $chance < $rank->crime_chance:
                $amount = Random::between(750, 1500);
                $message = Message::success($amount);
                break;
            default:
                //failed
                $message = Message::failure();
        }

        if ($amount)
        {
            $user->addCash($amount);
            $user->addExperience(50);
            $user->getCounter()->addOrganizedCrime();
        }

        // set cooldown
        $user->getCooldown()->setOrganizedCrime(Time::addMinutes(3));

        return $message;
    }
}
