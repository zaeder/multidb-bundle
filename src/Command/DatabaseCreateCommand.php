<?php

namespace Zaeder\MultiDb\Command;

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\DoctrineCommandHelper;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DatabaseCreateCommand extends CreateCommand
{
    use DoctrineSchemaTrait;

    public function __construct(ManagerRegistry $managerRegistry, EventDispatcherInterface $eventDispatcher, $name = null)
    {
        parent::__construct($name);
        $this->init($managerRegistry, $eventDispatcher);
    }

    protected function configure()
    {
        parent::configure();
        $this
            ->setName('app:database:create')
            ->addOption('serverkey', null, InputOption::VALUE_REQUIRED, 'The server key set for in table server');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        DoctrineCommandHelper::setZaeder\MultiDblicationEntityManager($this->getZaeder\MultiDblication(), $this->getEntityManagerName($input->getOption('serverkey')));

        return parent::execute($input, $output);
    }
}