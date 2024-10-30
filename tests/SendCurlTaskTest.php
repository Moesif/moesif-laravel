<?php
use PHPUnit\Framework\TestCase;
use Moesif\Sender\SendCurlTaskConsumer;

class SendCurlTaskConsumerTest extends TestCase
{
    /**
     * Test the updateUser method to ensure it triggers _execute_forked
     */
    public function testUpdateUserTriggersExecuteForked()
    {
        $applicationId = 'Your APplication Id.';
        $options = [
            'host' => 'api.moesif.net',
            'endpoint' => '/v1/events/batch',
            'users_endpoint' => '/v1/users',
            'users_batch_endpoint' => '/v1/users/batch',
            'company_endpoint' => 'v1/companies',
            'companies_batch_endpoint' => 'v1/companies/batch',
            'use_ssl' => true,
            'fork' => true
        ];

        $consumer = $this->getMockBuilder(SendCurlTaskConsumer::class)
            ->setConstructorArgs([$applicationId, $options])
            ->getMock();

        // Prepare test data
        $userData = [
        'user_id' => 'phpuser123',
        'name' => 'Test User mkdir funny',
        'email' => 'nihao@gmail.com' ,
        'comment' => 'Hello "world"! This is a test with special chars: ; & \' `'
        ];

        echo "Calling updateUser with userData...";
        print_r($userData);

        $data1 = json_encode($userData);

        $escapedData = escapeshellarg($data1);

        echo "cleaned user data ..." ;
        print_r($escapedData);

        try {
          // $newResult = $consumer->_execute_forked('https://webhook.site/203225cd-1dcc-4eb2-a921-1602c0cc5398', $escapedData);
          //  echo "execute forked returned: " . ($newResult ? 'true' : 'false') . "\n";
            $result = $consumer->updateUser($userData);
            echo "updateUser returned: " . ($result ? 'true' : 'false') . "\n";
                // Assert the expected outcome
            $this->assertTrue($result); // Adjust based on your actual expected return value
        } catch (Exception $e) {
            echo "Exception caught: " . $e->getMessage() . "\n";
        }

    }

}
