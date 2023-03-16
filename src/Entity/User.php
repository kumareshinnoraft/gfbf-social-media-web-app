<?php

namespace App\Entity;

use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userName = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(length: 255)]
    private ?string $gender = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $otp = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $otpCreationTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $accountCreationTime = null;

    #[ORM\Column]
    private ?bool $verified = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $lastActiveTime = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Likes::class)]
    private Collection $likes;

    public function __construct()
    {
        $this->likes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(?string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): self
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getOtp(): ?string
    {
        return $this->otp;
    }

    public function setOtp(?string $otp): self
    {
        $this->otp = $otp;

        return $this;
    }

    public function getOtpCreationTime(): ?\DateTimeInterface
    {
        return $this->otpCreationTime;
    }

    public function setOtpCreationTime(?\DateTimeInterface $otpCreationTime): self
    {
        $this->otpCreationTime = $otpCreationTime;

        return $this;
    }

    public function getAccountCreationTime(): ?\DateTimeInterface
    {
        return $this->accountCreationTime;
    }

    public function setAccountCreationTime(?\DateTimeInterface $accountCreationTime): self
    {
        $this->accountCreationTime = $accountCreationTime;

        return $this;
    }

    public function isVerified(): ?bool
    {
        return $this->verified;
    }

    public function setVerified(bool $verified): self
    {
        $this->verified = $verified;

        return $this;
    }  
    public function setUserDetails(string $fullName, string $userName, string $email, string $imageName, string $gender, string $password, string $otp, DateTimeImmutable $otpCreationTime, DateTimeImmutable $accountCreationTime, bool $verified){
      $this->setFullName($fullName);
      $this->setUserName($userName);
      $this->setEmail($email);
      $this->setImageName($imageName);
      $this->setGender($gender);
      $this->setPassword($password);
      $this->setOtp($otp);
      $this->setOtpCreationTime($otpCreationTime);
      $this->setAccountCreationTime($accountCreationTime);
      $this->setVerified($verified);
      $this->setIsActive(FALSE);
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getLastActiveTime(): ?\DateTimeInterface
    {
        return $this->lastActiveTime;
    }

    public function setLastActiveTime(?\DateTimeInterface $lastActiveTime): self
    {
        $this->lastActiveTime = $lastActiveTime;

        return $this;
    }

    /**
     * @return Collection<int, Likes>
     */
    public function getLikes(): Collection
    {
        return $this->likes;
    }

    public function addLike(Likes $like): self
    {
        if (!$this->likes->contains($like)) {
            $this->likes->add($like);
            $like->setUser($this);
        }

        return $this;
    }

    public function removeLike(Likes $like): self
    {
        if ($this->likes->removeElement($like)) {
            // set the owning side to null (unless already changed)
            if ($like->getUser() === $this) {
                $like->setUser(null);
            }
        }

        return $this;
    }
}
