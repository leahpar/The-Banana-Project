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
            // option to send to a specific user
            ->addOption('user', 'u', InputOption::VALUE_REQUIRED, 'User ID to send the message to')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $message = $input->getArgument('message') ?? 'Hello world!';
        $userId = $input->getOption('user');

        if ($userId) {
            $user = $this->em->getRepository(User::class)->find($userId);
            if ($user === null) {
                $io->error("User not found");
                return Command::FAILURE;
            }
            $users = [$user];
        }
        else {
            $users = $this->em->getRepository(User::class)->findAll();
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => 'bananaproject.inara.ovh',
                'publicKey' => $this->vapidPublicKey,
                'privateKey' => $this->vapidPrivateKey,
            ]
        ]);

        /** @var User $user */
        foreach ($users as $user) {
            $report = $webPush->sendOneNotification(
                Subscription::create($user->getSuscription()),
                json_encode([
                    'title' => 'The Banana Project',
                    'message' => $message,
                ]),
            );
            $endpoint = $report->getRequest()->getUri()->__toString();
            $endpoint = substr($endpoint, 0, 80) . '...';
            if ($report->isSuccess()) {
                $io->writeln("[{$user->id}] Message sent successfully for <comment>{$endpoint}</comment>");
            }
            elseif ($report->isSubscriptionExpired()) {
                // 404 or 410, unsubscribe
                $io->writeln("[{$user->id}] <error>KO</error> Subscription gone {$endpoint}: {$report->getReason()}");
                $this->em->remove($user);
            }
            else {
                $io->writeln("[{$user->id}] <error>KO</error> Message failed {$endpoint}: {$report->getReason()}");
            }
        }

//        /** @var User $user */
//        foreach ($users as $user) {
//            $webPush->queueNotification(
//                Subscription::create($user->getSuscription()),
//                json_encode([
//                    'title' => 'The Banana Project',
//                    'message' => $message,
//                ]),
//            );
//        }
//
//        foreach ($webPush->flush() as $report) {
//            $endpoint = $report->getRequest()->getUri()->__toString();
//            // truncate $endpoint
//            $endpoint = substr($endpoint, 0, 80) . '...';
//
//            if ($report->isSuccess()) {
//                $io->writeln("[] Message sent successfully for <comment>{$endpoint}</comment>");
//            } else {
//                $io->writeln("<error>KO</error> Message failed to sent for subscription {$endpoint}: {$report->getReason()}");
//            }
//        }

        $this->em->flush();
        $io->success('OK');

        return Command::SUCCESS;
    }
}
