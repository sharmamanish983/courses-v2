<?php

declare(strict_types=1);

namespace Galeas\Api\Console;

use Galeas\Api\BoundedContext\CreditCardProduct\Product\Command\DefineProductCommand;
use Galeas\Api\BoundedContext\CreditCardProduct\Product\CommandHandler\DefineProductHandler;
use Galeas\Api\Service\Logger\PhpOutLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DefineCreditCardProducts extends Command
{
    private DefineProductHandler $defineProductHandler;

    private string $serviceNameInLowercase;

    public function __construct(
        DefineProductHandler $defineProductHandler,
        string $serviceNameInLowercase
    ) {
        parent::__construct();

        $this->defineProductHandler = $defineProductHandler;
        $this->serviceNameInLowercase = $serviceNameInLowercase;
    }

    protected function configure(): void
    {
        try {
            $this->setName('galeas:define_credit_card_products')
                ->setDescription('Defines Credit Card Products');
        } catch (\Throwable $throwable) {
            return;
        }
    }
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        if ("credit_card_product" !== $this->serviceNameInLowercase) {
            return 0;
        }

        $defineStarterCard = new DefineProductCommand();
        $defineStarterCard->productIdentifierForAggregateIdHash = "STARTER_CREDIT_CARD";
        $defineStarterCard->name = "Starter";
        $defineStarterCard->interestInBasisPoints = 1200;
        $defineStarterCard->annualFeeInCents = 5000;
        $defineStarterCard->paymentCycle = "monthly";
        $defineStarterCard->creditLimitInCents = 50000;
        $defineStarterCard->maxBalanceTransferAllowedInCents = 0;
        $defineStarterCard->reward = "none";
        $defineStarterCard->cardBackgroundHex = "#7fffd4";
        $this->defineProductHandler->handle($defineStarterCard);


        $definePlatinumCard = new DefineProductCommand();
        $definePlatinumCard->productIdentifierForAggregateIdHash = "PLATINUM_CREDIT_CARD";
        $definePlatinumCard->name = "Platinum";
        $definePlatinumCard->interestInBasisPoints = 300;
        $definePlatinumCard->annualFeeInCents = 50000;
        $definePlatinumCard->paymentCycle = "monthly";
        $definePlatinumCard->creditLimitInCents = 500000;
        $definePlatinumCard->maxBalanceTransferAllowedInCents = 100000;
        $definePlatinumCard->reward = "points";
        $definePlatinumCard->cardBackgroundHex = "#E5E4E2";
        $this->defineProductHandler->handle($definePlatinumCard);

        return 0;
    }
}