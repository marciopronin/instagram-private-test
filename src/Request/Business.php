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
     * @return Response\InsightsResponse
     */
    public function getInsights(
        $day = null
    ) {
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
     * @return Response\MediaInsightsResponse
     */
    public function getMediaInsights(
        $mediaId
    ) {
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
     * @return Response\GraphqlResponse
     */
    public function getStatistics(
        $timezone = 'Atlantic/Canary',
        $activityTab = true,
        $audienceTab = true,
        $contentTab = true
    ) {
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
     * @return Response\GraphqlResponse
     */
    public function getPostInsights(
        $mediaId
    ) {
        return $this->ig->request('ads/graphql/')
            ->setSignedPost(false)
            ->setIsSilentFail(true)
            ->addParam('vc_policy', 'insights_policy')
            ->addPost('locale', $this->ig->getLocale())
            ->addPost('_uuid', $this->ig->uuid)
            // ->addPost('_csrftoken', $this->ig->client->getToken())
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
     * @return Response\GenericResponse
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
     * @return Response\BusinessWhitelistSettingsResponse
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
     * @return Response\BusinessWhitelistSettingsResponse
     *
     * @see https://help.instagram.com/116947042301556
     */
    public function updateWhitelistSettings(
        $addedUserIds = [],
        $removedUserIds = [],
        $requireApproval = true
    ) {
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
            // ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\BusinessWhitelistSettingsResponse());
    }

    /**
     * Converts back the business account to a profile account.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\BusinessWhitelistSettingsResponse
     */
    public function setConvertToPublicProfile()
    {
        return $this->ig->request('business/branded_content/update_whitelist_settings/')
            ->addPost('to_account_type', 1)
            ->addPost('_uid', $this->ig->account_id)
            // ->addPost('_csrftoken', $this->ig->client->getToken())
            ->addPost('_uuid', $this->ig->uuid)
            ->getResponse(new Response\BusinessWhitelistSettingsResponse());
    }

    /**
     * Get should require professional account.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\ShouldRequireProfessionalAccountResponse
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
     * @return Response\MonetizationProductsEligibilityDataResponse
     */
    public function getMonetizationProductsEligibilityData()
    {
        return $this->ig->request('business/eligibility/get_monetization_products_eligibility_data/')
            ->addParam('product_types', 'branded_content,user_pay')
            ->getResponse(new Response\MonetizationProductsEligibilityDataResponse());
    }

    /**
     * Get monetization products eligibility data.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return Response\MonetizationProductsEligibilityDataResponse
     */
    public function getMonetizationProductsGating()
    {
        return $this->ig->request('creators/partner_program/get_monetization_products_gating/')
            ->addParam('product_types', '')
            ->getResponse(new Response\MonetizationProductsEligibilityDataResponse());
    }

    /**
     * Get insights summary.
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return array
     */
    public function getInsightsSummary()
    {
        $response = $this->ig->request('bloks/apps/com.instagram.insights.account.timeframe.summary.container/')
            ->setSignedPost(false)
            ->addPost('target_id', $this->ig->account_id)
            ->addPost('screen_id', 100)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('bk_client_context', json_encode([
                'bloks_version' => Constants::BLOCK_VERSIONING_ID,
                'styles_id'     => 'instagram',
            ]))
            ->addPost('bloks_versioning_id', Constants::BLOCK_VERSIONING_ID)
            ->getResponse(new Response\GenericResponse());

        $responseArr = $response->asArray();
        $mainBloks = $this->ig->bloks->parseResponse($responseArr, '(bk.action.core.TakeLast');
        $firstDataBlok = null;
        foreach ($mainBloks as $mainBlok) {
            if (str_contains($mainBlok, 'insight_type') && str_contains($mainBlok, 'is_owner_viewing_target') && str_contains($mainBlok, 'duration_ms') && str_contains($mainBlok, 'start_timestamp')) {
                $firstDataBlok = $mainBlok;
            }
        }
        $parsed = $this->ig->bloks->parseBlok($firstDataBlok, 'bk.action.map.Make');
        $offsets = array_slice($this->ig->bloks->findOffsets($parsed, 'start_timestamp'), 0, -2);

        foreach ($offsets as $offset) {
            if (isset($parsed[$offset])) {
                $parsed = $parsed[$offset];
            } else {
                break;
            }
        }

        $firstMap = $this->ig->bloks->cleanData($this->ig->bloks->map_arrays($parsed[0], $parsed[1]));

        return $firstMap;
    }

    /**
     * Get common insights.
     *
     * NOT FINISHED YET. STILL RESEARCHING.
     *
     * @param mixed $startDate
     * @param mixed $endDate
     * @param mixed $period
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return array
     */
    public function getCommonInsights(
        $startDate,
        $endDate,
        $period
    ) {
        $response = $this->ig->request('bloks/apps/com.instagram.insights.common.date_picker.date_picker_surface/')
            ->setSignedPost(false)
            ->addPost('target_id', $this->ig->account_id)
            ->addPost('initial_start_date', $startDate)
            ->addPost('initial_end_date', $endDate)
            ->addPost('initial_period', $period)
            ->addPost('component_id', '1000445040')
            ->addPost('was_opened_in_screen', 1)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('logger_params_json', json_encode([
                'insight_type'              => 'account',
                'surface_type'              => 'account_overview',
                'container_type'            => 'time_range_selector',
                'target_id'                 => intval($this->ig->account_id),
                'ig_user_id'                => intval($this->ig->account_id),
                'is_owner_viewing_target'   => true,
                'origin'                    => 'unknown',
                'period'                    => $period,
                'unit'                      => 'date_picker',
                'EXPOSURE_LOGGER_THRESHOLD' => 0.3,
            ]))
            ->addPost('bk_client_context', json_encode([
                'bloks_version' => Constants::BLOCK_VERSIONING_ID,
                'styles_id'     => 'instagram',
            ]))
            ->addPost('bloks_versioning_id', Constants::BLOCK_VERSIONING_ID)
            ->getResponse(new Response\GenericResponse());

        $responseArr = $response->asArray();
        $mainBloks = $this->ig->bloks->parseResponse($responseArr, '(bk.action.core.TakeLast');
        $bloks = [];
        foreach ($mainBloks as $mainBlok) {
            $bloks[] = $this->ig->bloks->parseBlok($mainBlok, 'bk.action.map.Make');
        }

        return $bloks;
    }

    /**
     * Get insights interactions.
     *
     * NOT FINISHED YET. STILL RESEARCHING.
     *
     * @param mixed $period
     *
     * @throws \InstagramAPI\Exception\InstagramException
     *
     * @return array
     */
    public function getInsightsInteractions(
        $period
    ) {
        $response = $this->ig->request('bloks/apps/com.instagram.insights.account.content_interactions_breakout.timeframe.container/')
            ->setSignedPost(false)
            ->addPost('origin', 'unknown')
            ->addPost('period', 'THIS_WEEK')
            ->addPost('target_id', $this->ig->account_id)
            ->addPost('_uuid', $this->ig->uuid)
            ->addPost('bk_client_context', json_encode([
                'bloks_version' => Constants::BLOCK_VERSIONING_ID,
                'styles_id'     => 'instagram',
            ]))
            ->addPost('bloks_versioning_id', Constants::BLOCK_VERSIONING_ID)
            ->getResponse(new Response\GenericResponse());

        $responseArr = $response->asArray();
        $mainBloks = $this->ig->bloks->parseResponse($responseArr, '(bk.action.core.TakeLast');
        $bloks = [];
        foreach ($mainBloks as $mainBlok) {
            $bloks[] = $this->ig->bloks->parseBlok($mainBlok, 'bk.action.map.Make');
        }

        return $bloks;
    }
}
