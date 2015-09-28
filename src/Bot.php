<?php
namespace uww\cs\slackbot;

use React\EventLoop;
use React\Promise;
use Slack\DirectMessageChannel;
use Slack\Message\MessageBuilder;
use Slack\RealTimeClient;
use Slack\User;

class Bot
{
    private $token;
    private $client;

    public function __construct()
    {
        if (is_readable(dirname(__DIR__) . '/config.json')) {
            $config = json_decode(file_get_contents(dirname(__DIR__) . '/config.json'), true);
            $this->token = $config['slack-token'];
        }
    }

    public function run()
    {
        $loop = EventLoop\Factory::create();

        $this->client = new RealTimeClient($loop);
        $this->client->setToken($this->token);

        $this->client->on('team_join', function ($data) {
            $user = new User($this->client, $data['user']);
            $this->welcomeUser($user);
        });

        $this->client->connect()->then(function () {
            echo "Connected!\n";
        }, function (\Exception $e) {
            echo $e . PHP_EOL;
        });

        $loop->run();
    }

    /**
     * Welcomes a user to the team.
     *
     * @param array $data Event data.
     */
    protected function welcomeUser(User $user)
    {
        // Open a DM with the user and talk about the Slack team a bit.
        $this->client->getDMbyUserId($user->getId())->then(function (DirectMessageChannel $channel) {
            return $this->client->getDMById($channel->getId());
        })->then(function (DirectMessageChannel $channel) use ($user) {
            return $this->client->send(sprintf(
                "Hi there, %s! Thanks for signing up! I'm sure my friend <@USLACKBOT> is helping you set up your account right now.",
                $user->getUsername()
            ), $channel)->then(function () use ($user, $channel) {
                sleep(3);

                return $this->client->send("I just want to informally introduce you to the team."
                    . " First, you will see a list of channels on the left. There's the <#C025YTX9D>"
                    . " channel, which is a great place to discuss normal goings-on. If you want to just"
                    . " hang out and talk about random things, the <#C025YTX9F> channel is perfect for that.", $channel);
            })->then(function () use ($user, $channel) {
                sleep(6);

                return $this->client->send("If you're looking to start a group project, private groups are"
                    . " perfect for that. You can create a new group by clicking on the \"+\" next to \"PRIVATE"
                    . " GROUPS\" on the left.", $channel);
            })->then(function () use ($user, $channel) {
                sleep(5);

                return $this->client->send("If you have any questions, feel free to ask one of the humans in <#C025YTX9D>.", $channel);
            });
        });
    }
}
