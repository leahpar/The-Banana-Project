<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:vapid:generate',
    description: 'Generate VAPID keys',
)]
class VapidGenerateCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->newLine();
        $io->writeln('Generating new VAPID keys...');
        $io->comment("Write this in your <comment>.env.local</comment> file:");

        $vapid = \Minishlink\WebPush\VAPID::createVapidKeys();
        $io->writeln('VAPID_PUBLIC_KEY=' . $vapid['publicKey']);
        $io->writeln('VAPID_PRIVATE_KEY=' . $vapid['privateKey']);

        $io->success('OK');

        return Command::SUCCESS;
    }
}
