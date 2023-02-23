<?php

namespace InstagramAPI\Request;

use InstagramAPI\Constants;
use InstagramAPI\Response;

/**
 * Business-account related functions.
 *
 * These only work if you have a Business account.
 */
class Business extends RequestCollection
{
    /**
     * Get insights.
     *
     * @param $day
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\InsightsResponse
     */
    public function getInsights(
        $day = null)
    {
        if (empty($day)) {
            $day = date('d');
        }

        return $this->ig->request('insights/account_organic_insights/')
            ->addParam('show_promotions_in_landing_page', 'true')
            ->addParam('first', $day)
            ->getResponse(new Response\InsightsResponse());
    }

    /**
     * Get media insights.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MediaInsightsResponse
     */
    public function getMediaInsights(
        $mediaId)
    {
        return $this->ig->request("insights/media_organic_insights/{$mediaId}/")
            ->addParam('ig_sig_key_version', Constants::SIG_KEY_VERSION)
            ->getResponse(new Response\MediaInsightsResponse());
    }

    /**
     * Get account statistics.
     *
     * @param string $timezone    Timezone.
     * @param bool   $activityTab If its enabled or not.
     * @param bool   $audienceTab If its enabled or not.
     * @param bool   $contentTab  If its enabled or not.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GraphqlResponse
     */
    public function getStatistics(
        $timezone = 'Atlantic/Canary',
        $activityTab = true,
        $audienceTab = true,
        $contentTab = true)
    {
        return $this->ig->request('ads/graphql/')
            ->setSignedPost(false)
            ->setIsMultiResponse(true)
            ->addParam('locale', $this->ig->getLocale())
            ->addParam('vc_policy', 'insights_policy')
            ->addParam('surface', 'account')
            ->addPost('access_token', 'undefined')
            ->addPost('fb_api_caller_class', 'RelayModern')
            ->addPost('fb_api_req_friendly_name', 'IgInsightsAccountInsightsSurfaceQuery')
            ->addPost('variables', json_encode([
                'IgInsightsGridMediaImage_SIZE' => 360,
                'timezone'                      => $timezone,
                'activityTab'                   => $activityTab,
                'audienceTab'                   => $audienceTab,
                'contentTab'                    => $contentTab,
                'query_params'                  => json_encode([
                    'access_token'  => '',
                    'id'            => $this->ig->account_id,
                ]),
            ]))
            ->addPost('doc_id', '1926322010754880')
            ->getResponse(new Response\GraphqlResponse());
    }

    /**
     * Get account statistics.
     *
     * @param string $mediaId The media ID in Instagram's internal format (ie "3482384834_43294").
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GraphqlResponse
     */
    public function getPostInsights(
        $mediaId)
    {
        return $this->ig->request('ads/graphql/')
            ->setSignedPost(false)
            ->setIsSilentFail(true)
            ->addParam('vc_policy', 'insights_policy')
            ->addPost('locale', $this->ig->getLocale())
            ->addPost('_uuid', $this->ig->uuid)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('variables', json_encode([
                'surface'                       => 'post',
                'query_params'                  => json_encode([
                    'access_token'  => '',
                    'id'            => $mediaId,
                ]),
            ]))
            ->addPost('doc_id', '3067980219911174')
            ->getResponse(new Response\PostInsightsResponse());
    }

    /**
     * Get ads activity.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\GenericResponse
     */
    public function getAdsActivity()
    {
        return $this->ig->request('ads/ads_history/')
            ->setSignedPost(false)
            ->setIsSilentFail(true)
            ->addParam('_uuid', $this->ig->uuid)
            ->addPost('page_type', 94)
            ->addPost('ig_user_id', $this->ig->account_id)
            ->getResponse(new Response\GenericResponse());
    }

    /**
     * Get whitelist settings.
     *
     * Get's the list of users allowed to tag you in branded content.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\BusinessWhitelistSettingsResponse
     *
     * @see https://help.instagram.com/116947042301556
     */
    public function getWhitelistSettings()
    {
        return $this->ig->request('business/branded_content/get_whitelist_settings/')
            ->getResponse(new Response\BusinessWhitelistSettingsResponse());
    }

    /**
     * Update whitelist settings.
     *
     * Adds or removes from the list of users allowed to tag you in branded content.
     *
     * @param string[]|int[] $addedUserIds    Array of numerical UserPK IDs.
     * @param string[]|int[] $removedUserIds  Array of numerical UserPK IDs.
     * @param bool           $requireApproval Indicates if approval is required.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\BusinessWhitelistSettingsResponse
     *
     * @see https://help.instagram.com/116947042301556
     */
    public function updateWhitelistSettings(
        $addedUserIds = [],
        $removedUserIds = [],
        $requireApproval = true)
    {
        $userIds = count($addedUserIds) + count($removedUserIds);

        if ($userIds > 0) {
            foreach (array_merge($addedUserIds, $removedUserIds) as &$user) {
                if (!is_scalar($user)) {
                    throw new \InvalidArgumentException('User identifier must be scalar.');
                } elseif (!ctype_digit($user) && (!is_int($user) || $user < 0)) {
                    throw new \InvalidArgumentException(sprintf('"%s" is not a valid user identifier.', $user));
                }
                $user = (string) $user;
            }
        }

        return $this->ig->request('business/branded_content/update_whitelist_settings/')
            ->addPost('require_approval', $requireApproval)
            ->addPost('added_user_ids', $addedUserIds)
            ->addPost('removed_user_ids', $removedUserIds)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\BusinessWhitelistSettingsResponse());
    }

    /**
     * Converts back the business account to a profile account.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\BusinessWhitelistSettingsResponse
     */
    public function setConvertToPublicProfile()
    {
        return $this->ig->request('business/branded_content/update_whitelist_settings/')
            ->addPost('to_account_type', 1)
            ->addPost('_uid', $this->ig->account_id)
            //->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\BusinessWhitelistSettingsResponse());
    }

    /**
     * Get should require professional account.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\ShouldRequireProfessionalAccountResponse
     */
    public function getShouldRequireProfessionalAccount()
    {
        return $this->ig->request('business/branded_content/should_require_professional_account/')
            ->getResponse(new Response\ShouldRequireProfessionalAccountResponse());
    }

    /**
     * Get monetization products eligibility data.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return \InstagramAPI\Response\MonetizationProductsEligibilityDataResponse
     */
    public function getMonetizationProductsEligibilityData()
    {
        return $this->ig->request('business/eligibility/get_monetization_products_eligibility_data/')
            ->addParam('product_types', 'branded_content')
            ->getResponse(new Response\MonetizationProductsEligibilityDataResponse());
    }
}
