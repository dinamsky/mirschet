<?php

class shopBrandPluginFrontendAddReviewController extends waJsonController
{
	private $settings;

	public function __construct()
	{
		$settings_storage = new shopBrandSettingsStorage();
		$this->settings = $settings_storage->getSettings();
	}

	public function execute()
	{
		if (!$this->settings->is_enabled)
		{
			return;
		}

		$review = $this->getReview();

		if (count($this->errors))
		{
			return;
		}

		$review_storage = new shopBrandBrandReviewStorage();
		$user = wa()->getUser();

		if (waRequest::post('update_review') && $user->isAuth())
		{
			$existing_review = $review_storage->getUserBrandReview($user->getId(), $review['brand_id']);

			if ($existing_review)
			{
				$update_result = $review_storage->updateReview($existing_review->id, $review);

				if ($update_result)
				{
					$this->response['msg'] = 'Ваш отзыв обновлён';
				}
				else
				{
					$this->errors['save'] = 'Отзыв не обновлён';
				}

				return;
			}
		}


		if ($review_id = $review_storage->addReview($review))
		{
			$this->response['msg'] = 'Ваш отзыв добавлен';
		}
		else
		{
			$this->errors['save'] = 'Отзыв не сохранен';
		}
	}

	private function getReview()
	{
		$review = waRequest::post('review');

		/** @var shopConfig $config */
		$config = wa('shop')->getConfig();

		if (!$this->settings->disable_add_review_captcha && $config->getGeneralSettings('require_captcha') && !wa()->getCaptcha()->isValid())
		{
			$this->errors['captcha_refresh'] = 'Неверный код в captcha';
		}

		$user = wa()->getUser();
		if ($user->isAuth())
		{
			$review['auth_type'] = shopBrandBrandReview::AUTH_AUTH;
			$review['contact_id'] = $user->getId();
			$review['contact_name'] = $review['name'] ? $review['name'] : $user->getName();
			$review['contact_email'] = ifset($review['email']);
			$review['contact_phone'] = ifset($review['phone']);
		}
		else
		{
			$review['auth_type'] = shopBrandBrandReview::AUTH_GUEST;
			$review['contact_name'] = $review['name'];
		}

		$status = $this->settings->new_review_status;

		$review['status'] = $status ? $status : shopBrandBrandReview::STATUS_PUBLISHED;

		return $review;
	}
}