<?php
/**
 * Status behavior for models.
 *
 * @author	Veaceslav Medvedev <slavcopost@gmail.com>
 * @link	https://github.com/yiiext/status-behavior
 * @version 0.6
 */
class EStatusBehavior extends CActiveRecordBehavior
{
	/**
	 * @var string $statusField the name of the table field where data is stored.
	 * Required to set on init behavior. No default.
	 */
	public $statusField = null;

	/**
	 * @var array $statuses the possible statuses.
	 * Default: draft, published, archived.
	 * @see setStatuses
	 */
	public $statuses = array('draft', 'published', 'archived');

	protected $_status = null;
	protected $_statusText = 'unknown';

	/**
	 * Checks required properties and attaches the behavior object to the component.
	 *
	 * @param CActiveRecord $owner owner model.
	 * @throws CException if required properties are not set.
	 */
	public function attach($owner)
	{
		// Check required var statusField.
		if (!is_string($this->statusField) || empty($this->statusField)) {
			throw new CException(self::t(
				'yii',
				'Property "{class}.{property}" is not defined.',
				array('{class}' => get_class($this), '{property}' => 'statusField')
			));
		}

		parent::attach($owner);
	}

	/**
	 * @return string status value.
	 * @see getStatus
	 */
	public function __toString()
	{
		return $this->getStatus();
	}

	/**
	 * Init valid statuses values.
	 *
	 * @param array $statuses valid values for status.
	 * @return CActiveRecord owner model.
	 */
	public function setStatuses($statuses)
	{
		$this->statuses = is_array($statuses) && !empty($statuses) ? $statuses :
			array('draft', 'published', 'archived');

		return $this->getOwner();
	}

	/**
	 * @return string status value.
	 */
	public function getStatus()
	{
		return $this->_status;
	}

	/**
	 * @return string status text.
	 */
	public function getStatusText()
	{
		return $this->_statusText;
	}

	/**
	 * Set status for model.
	 *
	 * @param string $status status value or status text for model.
	 * @return CActiveRecord owner model.
	 * @throws CException if status is invalid.
	 */
	public function setStatus($status)
	{
		if (isset($this->statuses[$status])) {
			$this->_status = $status;
		} else {
			if (($this->_status = array_search($status, $this->statuses)) === false) {
				throw new CException(Yii::t(
					'yiiext',
					'Status "{status}" is not allowed.',
					array('{status}' => $status)
				));
			}
		}

		$this->_statusText = $this->statuses[$this->_status];
		$this->getOwner()->setAttribute($this->statusField, $this->_status);

		return $this->getOwner();
	}

	/**
	 * Save status. Will save only status attribute for model.
	 *
	 * @return boolean whether the saving succeeds.
	 */
	public function saveStatus()
	{
		return $this->getOwner()->save(true, array($this->statusField));
	}

	/**
	 * Load status after find model.
	 *
	 * @param CEvent $event the event parameter
	 */
	public function afterFind($event)
	{
		$this->_status = $this->getOwner()->getAttribute($this->statusField);
		$this->_statusText = isset($this->statuses[$this->_status]) ? $this->statuses[$this->_status] : 'unknown';

		parent::afterFind($event);
	}

	/**
	 * Has status.
	 *
	 * @param string|array $statuses the possible statuses.
	 * @return bool
	 */
	public function hasStatus($statuses = array())
	{
		if (!is_array($statuses)) {
			$statuses = explode(',', $statuses);
		}

		return in_array($this->getOwner()->getAttribute($this->statusField), $statuses);
	}

	/**
	 * Named scope.
	 *
	 * @param string|integer $status status.
	 * @return CActiveRecord owner model.
	 */
	public function status($status)
	{
		$criteria = $this->getOwner()->getDbCriteria();
		$column = $this->getOwner()->getDbConnection()->quoteColumnName($this->getOwner()->getTableAlias().'.'.$this->statusField);
		$criteria->addCondition($column.'='.CDbCriteria::PARAM_PREFIX.CDbCriteria::$paramCount);
		$criteria->params[CDbCriteria::PARAM_PREFIX.CDbCriteria::$paramCount++]=$status;

		return $this->getOwner();
	}
}
