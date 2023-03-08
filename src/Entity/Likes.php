<?php

namespace App\Entity;

use App\Repository\LikesRepository;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LikesRepository::class)]
class Likes
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\ManyToOne(inversedBy: 'likes')]
  #[ORM\JoinColumn(nullable: false)]
  private ?Post $post = null;

  #[ORM\ManyToOne(inversedBy: 'likes')]
  #[ORM\JoinColumn(nullable: false)]
  private ?User $user = null;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  private ?\DateTimeInterface $createdAt = null;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  private ?\DateTimeInterface $updatedAt = null;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getPost(): ?Post
  {
    return $this->post;
  }

  public function setPost(?Post $post): self
  {
    $this->post = $post;

    return $this;
  }

  public function getUser(): ?User
  {
    return $this->user;
  }

  public function setUser(?User $user): self
  {
    $this->user = $user;

    return $this;
  }

  public function getCreatedAt(): ?\DateTimeInterface
  {
    return $this->createdAt;
  }

  public function setCreatedAt(\DateTimeInterface $createdAt): self
  {
    $this->createdAt = $createdAt;

    return $this;
  }

  public function getUpdatedAt(): ?\DateTimeInterface
  {
    return $this->updatedAt;
  }

  public function setUpdatedAt(\DateTimeInterface $updatedAt): self
  {
    $this->updatedAt = $updatedAt;

    return $this;
  }

  public function setData(object $post, object $user, DateTime $createdTime, DateTime $updatedTime)
  {
    $this->setPost($post);
    $this->setUser($user);
    $this->setCreatedAt($createdTime);
    $this->setUpdatedAt($updatedTime);
  }

}