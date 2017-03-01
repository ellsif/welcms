<?php

namespace ellsif;

class SessionHandler implements \SessionHandlerInterface
{
  /**
   * Initialize session
   * @link http://php.net/manual/en/sessionhandlerinterface.open.php
   * @param string $save_path The path where to store/retrieve the session.
   * @param string $name The session name.
   * @return bool <p>
   * The return value (usually TRUE on success, FALSE on failure).
   * Note this value is returned internally to PHP for processing.
   * </p>
   * @since 5.4.0
   */
  public function open($save_path, $name)
  {
    return TRUE;
  }

  /**
   * Close the session
   * @link http://php.net/manual/en/sessionhandlerinterface.close.php
   * @return bool <p>
   * The return value (usually TRUE on success, FALSE on failure).
   * Note this value is returned internally to PHP for processing.
   * </p>
   * @since 5.4.0
   */
  public function close()
  {
    return TRUE;
  }

  /**
   * Read session data
   * @link http://php.net/manual/en/sessionhandlerinterface.read.php
   * @param string $session_id The session id to read data for.
   * @return string <p>
   * Returns an encoded string of the read data.
   * If nothing was read, it must return an empty string.
   * Note this value is returned internally to PHP for processing.
   * </p>
   * @since 5.4.0
   */
  public function read($session_id)
  {
    $dataAccess = getDataAccess();
    $sessions = $dataAccess->select('Session', 0, 1, '', ['sessid' => $session_id]);
    if (count($sessions) > 0) {
      return $sessions[0]['data'];
    } else {
      return '';  // sessionが無い
    }
  }

  /**
   * Write session data
   * @link http://php.net/manual/en/sessionhandlerinterface.write.php
   * @param string $session_id The session id.
   * @param string $session_data <p>
   * The encoded session data. This data is the
   * result of the PHP internally encoding
   * the $_SESSION superglobal to a serialized
   * string and passing it as this parameter.
   * Please note sessions use an alternative serialization method.
   * </p>
   * @return bool <p>
   * The return value (usually TRUE on success, FALSE on failure).
   * Note this value is returned internally to PHP for processing.
   * </p>
   * @since 5.4.0
   */
  public function write($session_id, $session_data)
  {
    $dataAccess = getDataAccess();
    $sessions = $dataAccess->select('Session', 0, 1, '', ['sessid' => $session_id]);
    if (count($sessions) > 0) {
      return $dataAccess->update('Session', intval($sessions[0]['id']), ['data' => $session_data]) > 0;
    } else {
      return $dataAccess->insert('Session', ['sessid' => $session_id, 'data' => $session_data]) > 0;
    }
  }

  /**
   * Destroy a session
   * @link http://php.net/manual/en/sessionhandlerinterface.destroy.php
   * @param string $session_id The session ID being destroyed.
   * @return bool <p>
   * The return value (usually TRUE on success, FALSE on failure).
   * Note this value is returned internally to PHP for processing.
   * </p>
   * @since 5.4.0
   */
  public function destroy($session_id)
  {
    $dataAccess = getDataAccess();
    return $dataAccess->deleteAll('Session', ['sessid' => $session_id]) > 0;
  }

  /**
   * Cleanup old sessions
   * @link http://php.net/manual/en/sessionhandlerinterface.gc.php
   * @param int $maxlifetime <p>
   * Sessions that have not updated for
   * the last maxlifetime seconds will be removed.
   * </p>
   * @return bool <p>
   * The return value (usually TRUE on success, FALSE on failure).
   * Note this value is returned internally to PHP for processing.
   * </p>
   * @since 5.4.0
   */
  public function gc($maxlifetime)
  {
    // TODO: Implement gc() method.
    return true;
  }

}