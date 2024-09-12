<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Identity\User\CommandHandler;

use Galeas\Api\BoundedContext\Identity\User\Command\SignUp;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\IsEmailTaken;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\IsUsernameTaken;
use Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\SignUpHandler;
use Galeas\Api\BoundedContext\Identity\User\Event\SignedUp;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\InvalidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Email\ValidEmails;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\InvalidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Security\ValidPasswords;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\InvalidUsernames;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Username\ValidUsernames;

class SignUpHandlerTest extends HandlerTestBase
{
    /**
     * @test
     */
    public function testHandle(): void
    {
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithCallback(
                IsEmailTaken::class,
                'isEmailTaken',
                function (string $email): bool {
                    if (ValidEmails::listValidEmails()[0] === $email) {
                        return false;
                    }

                    return true;
                }
            ),
            $this->mockForCommandHandlerWithCallback(
                IsUsernameTaken::class,
                'isUsernameTaken',
                function (string $username): bool {
                    if (ValidUsernames::listValidUsernames()[0] === $username) {
                        return false;
                    }

                    return true;
                }
            )
        );

        $command = new SignUp();
        $command->primaryEmail = ValidEmails::listValidEmails()[0];
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->username = ValidUsernames::listValidUsernames()[0];
        $command->termsOfUseAccepted = true;
        $command->metadata = $this->mockMetadata();

        $response = $handler->handle($command);

        /** @var SignedUp $storedEvent */
        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[0];
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[0];

        Assert::assertEquals(
            $storedEvent,
            $queuedEvent
        );

        Assert::assertEquals(
            $command->primaryEmail,
            $storedEvent->primaryEmail()
        );
        Assert::assertTrue(
            password_verify(
                $command->password,
                $storedEvent->hashedPassword()
            )
        );
        Assert::assertEquals(
            $command->username,
            $storedEvent->username()
        );
        Assert::assertEquals(
            $command->termsOfUseAccepted,
            $storedEvent->termsOfUseAccepted()
        );
        Assert::assertEquals(
            $command->metadata,
            $storedEvent->eventMetadata()
        );
        Assert::assertEquals(
            [
                'userId' => $storedEvent->aggregateId()->id(),
            ],
            $response
        );
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\InvalidEmail
     */
    public function testInvalidEmail(): void
    {
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                IsEmailTaken::class,
                'isEmailTaken',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new SignUp();
        $command->primaryEmail = InvalidEmails::listInvalidEmails()[0];
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->username = ValidUsernames::listValidUsernames()[0];
        $command->termsOfUseAccepted = true;
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\InvalidPassword
     */
    public function testInvalidPassword(): void
    {
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                IsEmailTaken::class,
                'isEmailTaken',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new SignUp();
        $command->primaryEmail = ValidEmails::listValidEmails()[0];
        $command->password = InvalidPasswords::listInvalidPasswords()[0];
        $command->username = ValidUsernames::listValidUsernames()[0];
        $command->termsOfUseAccepted = true;
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\InvalidUsername
     */
    public function testInvalidUsername(): void
    {
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                IsEmailTaken::class,
                'isEmailTaken',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new SignUp();
        $command->primaryEmail = ValidEmails::listValidEmails()[0];
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->username = InvalidUsernames::listInvalidUsernames()[0];
        $command->termsOfUseAccepted = true;
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\TermsAreNotAgreedTo
     */
    public function testTermsAreNotAgreedTo(): void
    {
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                IsEmailTaken::class,
                'isEmailTaken',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new SignUp();
        $command->primaryEmail = ValidEmails::listValidEmails()[0];
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->username = ValidUsernames::listValidUsernames()[0];
        $command->termsOfUseAccepted = false;
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\EmailIsTaken
     */
    public function testEmailIsTaken(): void
    {
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                IsEmailTaken::class,
                'isEmailTaken',
                true
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                false
            )
        );

        $command = new SignUp();
        $command->primaryEmail = ValidEmails::listValidEmails()[0];
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->username = ValidUsernames::listValidUsernames()[0];
        $command->termsOfUseAccepted = true;
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Identity\User\CommandHandler\SignUp\UsernameIsTaken
     */
    public function testUsernameIsTaken(): void
    {
        $handler = new SignUpHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                IsEmailTaken::class,
                'isEmailTaken',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsUsernameTaken::class,
                'isUsernameTaken',
                true
            )
        );

        $command = new SignUp();
        $command->primaryEmail = ValidEmails::listValidEmails()[0];
        $command->password = ValidPasswords::listValidPasswords()[0];
        $command->username = ValidUsernames::listValidUsernames()[0];
        $command->termsOfUseAccepted = true;
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }
}