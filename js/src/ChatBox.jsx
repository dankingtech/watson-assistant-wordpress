/* global jQuery */

import React, {Component} from 'react';
import ReactTooltip from 'react-tooltip-currenttarget';
import webrtc from 'webrtcsupport';
import jstz from 'jstz';
import merge from "deepmerge";

import MessageGroup from './MessageGroup';
import InputBox from './InputBox.jsx';
import CallInterface from './CallInterface.jsx';

import 'whatwg-fetch';

export default class ChatBox extends Component {
    constructor(props) {
        super(props);

        if (typeof(localStorage) !== 'undefined' && localStorage.getItem('watson_bot_state')) {
            this.loadStateFromStorage();
        } else {
            this.state = {
                messages: [],
                context: {},
                showCallInterface: false,
                mediaSecure: true,
                convStarted: false
            };
        }
        this.state.context = merge(
            this.state.context,
            this.getInitialContext()
        );

        this.loadedMessages = this.state.messages.length;
    }

    getInitialContext() {
        return merge(
            watsonconvSettings.context,
            {
                global: {
                    system: {
                        timezone: jstz.determine().name()
                    }
                }
            }
        );
    }

    loadStateFromStorage() {
        this.state = JSON.parse(localStorage.getItem('watson_bot_state'));
        if (!this.state.context) {
            this.state.context = {};
        }
    }

    componentDidMount() {
        // If conversation already exists, scroll to bottom, otherwise start conversation.
        if (typeof(this.messageList) !== 'undefined') {
            this.scrollToBottom();
            // this.messageList.scrollTop = this.messageList.scrollHeight;
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

        window.addEventListener('storage', function(e) {
            if(e.key === 'watson_bot_state') {
                this.loadStateFromStorage();
                this.setState(this.state);
                this.scrollToBottom();
            };
        }.bind(this));
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

    sendMessage(message, fullBody = false) {
        if (!this.state.convStarted) {
            this.setState({convStarted: true});
        }

        let sendBody;

        if (fullBody) {
            sendBody = message;

            if (typeof sendBody.context === 'object') {
                sendBody.context = merge(this.state.context, sendBody.context);
            } else {
                sendBody.context = this.state.context;
            }
        } else {
            sendBody = {
                input: {text: message},
                context: this.state.context
            };
        }
        if (sendBody.input) {
            sendBody.input = merge(sendBody.input, {
                options: {
                    return_context: true
                }
            });
        }
        sendBody.session_id = this.state.session_id;

        fetch(watsonconvSettings.apiUrl, {
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': watsonconvSettings.nonce
            },
            credentials: 'same-origin',
            method: 'POST',
            body: JSON.stringify(sendBody)
        }).then(response => {
            if (!response.ok) {
                throw Error('Message could not be sent.');
            }
            return response.json();
        }).then(body => {
            let {generic} = body.output;

            this.setState({
                context: body.context,
                messages: this.state.messages.concat({
                    from: 'watson',
                    content: generic,
                    options: body.output.options
                }),
                session_id: body.session_id
            }, this.saveState.bind(this));
        }).catch(error => {
            console.log(error);
        });

        if (message) {
            this.setState({
                messages: this.state.messages.concat({
                    from: 'user',
                    text: fullBody ? message.input.text : message
                })
            });
        }
    }

    reset() {
        this.setState({
            messages: [],
            context: this.getInitialContext(),
            session_id: null
        });

        this.sendMessage();

        this.loadedMessages = this.state.messages.length;
    }

    saveState() {
        if (typeof(localStorage) !== 'undefined') {
            localStorage.setItem('watson_bot_state', JSON.stringify(this.state))
        }
    }

    render() {
        let {callConfig, clearText} = watsonconvSettings;

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
                    <ReactTooltip/>
                    <div className='overflow-hidden watson-font'>{watsonconvSettings.title}</div>
                </div>
                <div id="chatbox-body">
                    {hasNumber && showCallInterface &&
                    <CallInterface allowTwilio={allowTwilio}/>}
                    <div id='message-container'>
                        <div id='messages' ref={div => {
                            this.messageList = div
                        }}>
                            {this.state.messages.map(
                                (message, index) =>
                                    <MessageGroup
                                        {...message}
                                        key={index}
                                        index={index}
                                        showPauses={index >= this.loadedMessages}
                                        sendMessage={this.sendMessage.bind(this)}
                                        scroll={this.scrollToBottom.bind(this)}
                                    />
                            )}
                        </div>
                    </div>
                    <InputBox sendMessage={this.sendMessage.bind(this)}/>
                </div>
            </div>
        );
    }
}
