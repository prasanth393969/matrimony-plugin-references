<?php

namespace ACPT\Core\CQRS\Query;

use ACPT\Admin\ACPT_License_Manager;
use ACPT\Utils\Http\ACPTApiClient;
use ACPT\Utils\PHP\IP;

class FetchLicenseQuery implements QueryInterface
{
	/**
	 * @return array|mixed|void
	 * @throws \Exception
	 */
	public function execute()
	{
		$currentVersion = ACPT_PLUGIN_VERSION;
		$licenseActivation = ACPT_License_Manager::getLicense();
		$activation = ACPTApiClient::call('/license/activation/fetch', [
			'id' => $licenseActivation['activation_id'],
            'enable_beta' => ACPT_ENABLE_BETA,
		]);

        if(empty($activation)){
            throw new \Exception("No response from the server");
        }

		if(isset($activation['error'])){
		    throw new \Exception($activation['error']);
        }

		unset($licenseActivation['license']);

		$versionInfo = [
			'currentVersion' => $currentVersion,
			'licenseActivation' => $licenseActivation,
			'activationLink' => $this->getActivationLink(),
		];

		return array_merge($activation, $versionInfo);
	}

    /**
     * @return string
     */
	private function getActivationLink()
    {
        $referer = json_encode([
                'site' => site_url(),
                'siteName' => get_bloginfo('name'),
                'ip' => IP::getClientIP(),
        ]);

        return "https://acpt.io/activation?referer=".base64_encode($referer);
    }
}