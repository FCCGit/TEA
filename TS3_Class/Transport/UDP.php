<?php

/**
 * @file
 * TeamSpeak 3 PHP Framework
 *
 * $Id: UDP.php 95 2011-04-17 14:23:54Z chris_smith96@hotmail.com $
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package   TeamSpeak3
 * @version   1.1.5-beta
 * @author    Sven 'ScP' Paulsen
 * @copyright Copyright (c) 2010 by Planet TeamSpeak. All rights reserved.
 */

/**
 * @class TeamSpeak3_Transport_UDP
 * @brief Class for connecting to a remote server through UDP.
 */
class TeamSpeak3_Transport_UDP extends TeamSpeak3_Transport_Abstract
{
  /**
   * Connects to a remote server.
   *
   * @throws TeamSpeak3_Transport_Exception
   * @return void
   */
  public function connect()
  {
    if($this->stream !== null) return;

    $host = strval($this->config["host"]);
    $port = strval($this->config["port"]);

    $address = "udp://" . $host . ":" . $port;
    $timeout = intval($this->config["timeout"]);

    $this->stream = @stream_socket_client($address, $errno, $errstr, $timeout);

    if($this->stream === FALSE)
    {
      throw new TeamSpeak3_Transport_Exception(utf8_encode($errstr), $errno);
    }

    @stream_set_timeout($this->stream, $timeout);
    @stream_set_blocking($this->stream, $this->config["blocking"] ? 1 : 0);
  }

  /**
   * Disconnects from a remote server.
   *
   * @return void
   */
  public function disconnect()
  {
    if($this->stream === null) return;

    $this->stream = null;

    TeamSpeak3_Helper_Signal::getInstance()->emit(strtolower($this->getAdapterType()) . "Disconnected");
  }

  /**
   * Reads data from the stream.
   *
   * @param  integer $length
   * @throws TeamSpeak3_Transport_Exception
   * @return TeamSpeak3_Helper_String
   */
  public function read($length = 4096)
  {
    $this->connect();
    $this->waitForReadyRead();

    $data = @fread($this->stream, $length);

    TeamSpeak3_Helper_Signal::getInstance()->emit(strtolower($this->getAdapterType()) . "DataRead", $data);

    if($data === FALSE)
    {
      throw new TeamSpeak3_Transport_Exception("connection to server '" . $this->config["host"] . ":" . $this->config["port"] . "' lost");
    }

    return new TeamSpeak3_Helper_String($data);
  }

  /**
   * Writes data to the stream.
   *
   * @param  string $data
   * @return void
   */
  public function send($data)
  {
    $this->connect();

    @stream_socket_sendto($this->stream, $data);

    TeamSpeak3_Helper_Signal::getInstance()->emit(strtolower($this->getAdapterType()) . "DataSend", $data);
  }
}
