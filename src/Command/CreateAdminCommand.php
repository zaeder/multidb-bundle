<?php

namespace Zaeder\MultiDb\Command;

use Zaeder\MultiDb\Entity\Local\User;
use Zaeder\MultiDb\Security\PasswordEncoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateAdminCommand
 * @package Zaeder\MultiDb\Command
 */
class CreateAdminCommand extends Command
{
    /**
     * @var EntityManagerInterface
     */
    private $localEntityManager;
    /**
     * @var PasswordEncoder
     */
    private $encoder;
    /**
     * @var string
     */
    protected static $defaultName = 'app:create-admin';

    /**
     * CreateAdminCommand constructor.
     * @param EntityManagerInterface $localEntityManager
     * @param PasswordEncoder $encoder
     */
    public function __construct(EntityManagerInterface $localEntityManager, PasswordEncoder $encoder)
    {
        $this->localEntityManager = $localEntityManager;
        $this->encoder = $encoder;

        parent::__construct();
    }

    /**
     * Command configuration
     */
    protected function configure()
    {
        $this
            ->setDescription('Creates a new admin.')
            ->setHelp('This command allows you to create a user...')
            ->addArgument('username', InputArgument::REQUIRED, 'Admin username')
            ->addArgument('password', InputArgument::REQUIRED, 'Admin password')
            ->addArgument('email', InputArgument::REQUIRED, 'Admin email')
        ;
    }

    /**
     * Execute admin account creation
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = new User();
        $user
            ->setUsername($input->getArgument('username'))
            ->setEmail($input->getArgument('email'))
            ->setPassword($this->encoder->encode($input->getArgument('password')))
            ->setSalt($this->encoder->getIv());

        $this->localEntityManager->persist($user);
        $this->localEntityManager->flush();
    }
}