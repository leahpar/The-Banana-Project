<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:push',
    description: 'Send push notifications',
)]
class PushCommand extends Command
{

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly string $vapidPublicKey,
        private readonly string $vapidPrivateKey,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    // configure
    protected function configure(): void
    {
        $this
            ->addArgument('message', InputArgument::OPTIONAL, 'Message to send (default: "Hello world!")')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $message = $input->getArgument('message') ?? 'Hello world!';

        $users = $this->em->getRepository(User::class)->findAll();

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => 'webpush.local',
                'publicKey' => $this->vapidPublicKey,
                'privateKey' => $this->vapidPrivateKey,
            ]
        ]);

        /** @var User $user */
        foreach ($users as $user) {
            $webPush->queueNotification(
                Subscription::create($user->getSuscription()),
                json_encode([
                    'title' => 'The Banana Project',
                    'message' => $message,
                ]),
            );
        }

        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            // truncate $endpoint
            $endpoint = substr($endpoint, 0, 80) . '...';

            if ($report->isSuccess()) {
                $io->writeln("Message sent successfully for <comment>{$endpoint}</comment>");
            } else {
                $io->writeln("<error>KO</error> Message failed to sent for subscription {$endpoint}: {$report->getReason()}");
            }
        }

        $io->success('OK');

        return Command::SUCCESS;
    }
}
