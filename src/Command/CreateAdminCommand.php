<?php

namespace Zaeder\MultiDbBundle\Command;

use Doctrine\Common\Persistence\ManagerRegistry;
use Zaeder\MultiDbBundle\Entity\Local\User;
use Zaeder\MultiDbBundle\Security\PasswordEncoder;
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
    protected $localEntityManager;
    /**
     * @var PasswordEncoder
     */
    protected $encoder;
    /**
     * @var string
     */
    protected static $defaultName = 'app:create-admin';

    /**
     * CreateAdminCommand constructor.
     * @param ManagerRegistry $registry
     * @param string $localEntityManagerName
     * @param PasswordEncoder $encoder
     */
    public function __construct(
        ManagerRegistry $registry,
        string $localEntityManagerName,
        PasswordEncoder $encoder
    )
    {
        $this->localEntityManager = $registry->getManager($localEntityManagerName);
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