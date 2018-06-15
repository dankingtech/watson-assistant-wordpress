/* global jQuery */

import React, { Component } from 'react';
import ReactTooltip from 'react-tooltip-currenttarget';
import webrtc from 'webrtcsupport';
import jstz from 'jstz';

import MessageGroup from './MessageGroup';
import InputBox from './InputBox.jsx';
import CallInterface from './CallInterface.jsx';

import 'whatwg-fetch';

export default class ChatBox extends Component {
  constructor(props) {
    super(props);

    if (typeof(sessionStorage) !== 'undefined' &&
        sessionStorage.getItem('watson_bot_state'))
    {
      this.state = JSON.parse(sessionStorage.getItem('watson_bot_state'));
      Object.assign(this.state.context, watsonconvSettings.context, {timezone: jstz.determine().name()});
    } else {
      this.state = {
        messages: [],
        context: watsonconvSettings.context,
        showCallInterface: false,
        mediaSecure: true,
        convStarted: false
      };
      Object.assign(this.state.context, {timezone: jstz.determine().name()});
    }
  }

  componentDidMount() {
    // If conversation already exists, scroll to bottom, otherwise start conversation.
    if (typeof(this.messageList) !== 'undefined') {
      this.messageList.scrollTop = this.messageList.scrollHeight;
    }

    if (!this.state.convStarted && !this.props.isMinimized) {
      this.sendMessage();
    }
    
    if (webrtc.support && 'https:' !== document.location.protocol) {
      navigator.mediaDevices.getUserMedia({video: {width: {min: 2, max: 1}}})
      .then(stream => {
        console.log("getUserMedia detection failed");
        stream.getTracks().forEach(t => t.stop());
      })
      .catch(e => {
        switch (e.name) {
          case "NotSupportedError":
          case "NotAllowedError":
          case "SecurityError":
            console.log("Can't access microphone in http");
            this.setState({mediaSecure: false});
            break;
          case "OverconstrainedError":
          default:
            break;
        }
      });
    }
  }

  componentDidUpdate(prevProps, prevState) {
    if (!this.state.convStarted && !this.props.isMinimized) {
      this.sendMessage();
    }

    if (prevState.messages.length !== this.state.messages.length) {
      // Ensure that chat box stays scrolled to bottom
      if (typeof(this.messageList) !== 'undefined') {
        this.scrollToBottom()
      }
    }
  }

  toggleCallInterface() {
    this.setState({showCallInterface: !this.state.showCallInterface});
  }

  scrollToBottom() {
    jQuery(this.messageList).stop().animate({scrollTop: this.messageList.scrollHeight});
  }

  sendMessage(message) {
    if (!this.state.convStarted) {
      this.setState({convStarted: true});
    }

    fetch(watsonconvSettings.apiUrl, {
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': watsonconvSettings.nonce
      },
      credentials: 'same-origin',
      method: 'POST',
      body: JSON.stringify({
        input: {text: message},
        context: this.state.context
      })
    }).then(response => {
      if (!response.ok) {
          throw Error('Message could not be sent.');
      }
      return response.json();
    }).then(body => {
      let { text } = body.output;

      this.setState({
        context: body.context,
        messages: this.state.messages.concat({
          from: 'watson',
          text: Array.isArray(text) ? text : [text], 
          options: body.output.options,
          loadedMessages: (watsonconvSettings.typingDelay === 'yes') ? 0 : text.length
        })
      }, this.saveState.bind(this));
    }).catch(error => {
      console.log(error);
    });

    if (message) {
      this.setState({
        messages: this.state.messages.concat({from: 'user', text: [message], loadedMessages: 1})
      });
    }
  }

  reset() {
    this.setState({
      messages: [],
      context: null
    });
    
    this.sendMessage();
  }

  incLoadedMessages(index) {
    let messages = this.state.messages.slice();
    messages[index] = {...messages[index], loadedMessages: messages[index].loadedMessages + 1};
    this.setState({messages: messages}, this.saveState.bind(this));
  }

  saveState() {
    if (typeof(sessionStorage) !== 'undefined') {
      sessionStorage.setItem('watson_bot_state', JSON.stringify(this.state))
    }
  }

  render() {
    let { callConfig, clearText } = watsonconvSettings;

    let position = watsonconvSettings.position || ['bottom', 'right'];

    let showCallInterface = this.state.showCallInterface;
    let allowTwilio = callConfig.useTwilio == 'yes'
                    && callConfig.configured
                    && webrtc.support 
                    && this.state.mediaSecure;
    
    let hasNumber = Boolean(callConfig.recipient);

    return (
      <div id='watson-box' className='drop-shadow animated'>
        <div
          id='watson-header'
          className='watson-font'
        >
          <span 
            className={`dashicons 
              dashicons-arrow-${position[0] == 'bottom' ? 'down' : 'up'}-alt2 
              header-button minimize-button`}
            onClick={this.props.toggleMinimize}
            ></span>
          <span
            onClick={this.reset.bind(this)} 
            className={`dashicons dashicons-trash header-button`}
            data-tip={clearText || 'Clear Messages'}>
          </span>
          {hasNumber &&
            <span
              onClick={this.toggleCallInterface.bind(this)} 
              className={`dashicons dashicons-phone header-button`}
              data-tip={callConfig.callTooltip || 'Talk to a Live Agent'}>
            </span>
          }
          <ReactTooltip />
          <div className='overflow-hidden watson-font'>{watsonconvSettings.title}</div>
        </div>
        <div id="chatbox-body">
          {hasNumber && showCallInterface && 
            <CallInterface allowTwilio={allowTwilio} />}
          <div id='message-container'>
            <div id='messages' ref={div => {this.messageList = div}}>
              {this.state.messages.map(
                (message, index) => 
                  <MessageGroup 
                    message={message}
                    key={index}
                    index={index}
                    sendMessage={this.sendMessage.bind(this)}
                    incLoaded={this.incLoadedMessages.bind(this)}
                    scroll={this.scrollToBottom.bind(this)}
                  />
              )}
            </div>
          </div>
          <InputBox sendMessage={this.sendMessage.bind(this)} />
        </div>
      </div>
    );
  }
}
