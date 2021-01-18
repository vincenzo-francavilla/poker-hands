<?php

namespace App\Entity;

use App\Repository\TournamentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TournamentRepository::class)
 */
class Tournament
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Players", inversedBy="playerId")
     */
    private $playerOneId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Players", inversedBy="playerId")
     */
    private $playerTwoId;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MatchData", inversedBy="id")
     */
    private $matchDataId;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPlayerOneId()
    {
        return $this->playerOneId;
    }

    /**
     * @param mixed $playerOneId
     */
    public function setPlayerOneId($playerOneId): void
    {
        $this->playerOneId = $playerOneId;
    }

    /**
     * @return mixed
     */
    public function getPlayerTwoId()
    {
        return $this->playerTwoId;
    }

    /**
     * @param mixed $playerTwoId
     */
    public function setPlayerTwoId($playerTwoId): void
    {
        $this->playerTwoId = $playerTwoId;
    }

    /**
     * @return mixed
     */
    public function getMatchDataId()
    {
        return $this->matchDataId;
    }

    /**
     * @param mixed $matchDataId
     */
    public function setMatchDataId($matchDataId): void
    {
        $this->matchDataId = $matchDataId;
    }
}
