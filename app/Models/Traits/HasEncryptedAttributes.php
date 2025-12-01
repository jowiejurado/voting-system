<?php

namespace App\Models\Traits;

use Illuminate\Support\Facades\Crypt;

trait HasEncryptedAttributes
{
	/**
	 * Set a given attribute on the model.
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return $this
	 */
	public function setAttribute($key, $value)
	{
		if ($this->isEncryptable($key) && ! is_null($value)) {
			if (is_array($value) || is_object($value)) {
				$value = json_encode($value);
			}

			$value = Crypt::encryptString($value);
		}

		return parent::setAttribute($key, $value);
	}

	/**
	 * Get an attribute from the model.
	 *
	 * @param  string  $key
	 * @return mixed
	 */
	public function getAttribute($key)
	{
		$value = parent::getAttribute($key);

		if ($this->isEncryptable($key) && ! is_null($value)) {
			try {
				$value = Crypt::decryptString($value);

				$decoded = json_decode($value, true);
				if (json_last_error() === JSON_ERROR_NONE) {
					return $decoded;
				}

				return $value;
			} catch (\Exception $e) {
				// If decryption fails (old/plain data), just return raw value
				return $value;
			}
		}

		return $value;
	}

	/**
	 * Check if the given key is encryptable.
	 *
	 * @param  string  $key
	 * @return bool
	 */
	protected function isEncryptable(string $key): bool
	{
		return property_exists($this, 'encryptable')
			&& is_array($this->encryptable)
			&& in_array($key, $this->encryptable, true);
	}
}
