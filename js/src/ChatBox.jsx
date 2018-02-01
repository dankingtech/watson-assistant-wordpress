import React, { Component } from 'react';
import Draggable from 'react-draggable';
import { TransitionGroup } from 'react-transition-group';
import ReactTooltip from 'react-tooltip-currenttarget';
import webrtc from 'webrtcsupport';

import Message from './Message.jsx';
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
    } else {
      this.state = {
        messages: [],
        context: null,
        showCallInterface: false,
        mediaSecure: true,
        convStarted: false
      };
    }
  }

  componentDidMount(props) {
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
      if (typeof(sessionStorage) !== 'undefined') {
        sessionStorage.setItem('watson_bot_state', JSON.stringify(this.state))
      }
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

    fetch('?rest_route=/watsonconv/v1/message', {
      headers: {
        'Content-Type': 'application/json'
      },
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
      this.setState({
        context: body.context,
        messages: this.state.messages.concat({
          from: 'watson',
          text: body.output.text, 
          options: body.output.options
        })
      });
    }).catch(error => {
      console.log(error);
    });

    if (message) {
      this.setState({
        messages: this.state.messages.concat({from: 'user', text: message})
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

  render() {
    var position = this.props.position || ['bottom', 'right'];
    var showCallInterface = this.state.showCallInterface;

    var allowTwilio = this.props.callConfig.useTwilio == 'yes'
                    && this.props.callConfig.configured
                    && webrtc.support 
                    && this.state.mediaSecure;
    
    var hasNumber = Boolean(this.props.callConfig.recipient);

    return (
      <div id='watson-box' className='drop-shadow animated'>
        <div
          id='watson-header'
          class='watson-font'
        >
          <span className={`dashicons dashicons-arrow-${
              position[0] == 'bottom' ? 'down' : 'up'
            }-alt2 popup-control`}></span>
          {hasNumber &&
            <span
              onClick={this.toggleCallInterface.bind(this)} 
              className={`dashicons dashicons-phone header-button`}
              data-tip={this.props.callConfig.callTooltip || 'Talk to a Live Agent'}>
            </span>
          }
          <ReactTooltip />
          <div className='overflow-hidden watson-font'>{this.props.title}</div>
        </div>
        <div style={{position: 'relative', height: '100%', 'display': 'flex', 'flex-direction': 'column'}}>
          {hasNumber && showCallInterface && 
            <CallInterface allowTwilio={allowTwilio} callConfig={this.props.callConfig} />}
          <div id='message-container'>
            <div id='messages' ref={div => {this.messageList = div}}>
              <div style={{'text-align': 'right', margin: '-5 0 5 10'}} className='watson-font'>
                <a style={{'font-size': '0.85em'}} onClick={this.reset.bind(this)}>Clear Messages</a>
              </div>
              {this.state.messages.map(
                (message, index) => <Message message={message} key={index} sendMessage={this.sendMessage.bind(this)} />
              )}
            </div>
          </div>
          <InputBox sendMessage={this.sendMessage.bind(this)} />
        </div>
      </div>
    );
  }
}
