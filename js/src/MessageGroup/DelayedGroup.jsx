import React, { Component } from 'react';

export default class DelayedGroup extends Component {
  componentDidMount() {
    if (this.props.message.loadedMessages < this.props.message.text.length) {
      this.simulateTyping();
    }
  }

  shouldComponentUpdate(nextProps) {
    return nextProps.message.loadedMessages !== this.props.message.loadedMessages;
  }

  componentDidUpdate(prevProps) {
    this.props.scroll();

    let prevLoadedMessages = prevProps.message.loadedMessages;
    let loadedMessages = this.props.message.loadedMessages;
    let numMessages = this.props.message.text.length;

    if (prevLoadedMessages !== loadedMessages && loadedMessages < numMessages) {
      this.simulateTyping();
    }
  }

  simulateTyping() {
    setTimeout(() => {
      this.props.incLoaded(this.props.index);
    }, Math.min(this.props.message.text[this.props.message.loadedMessages].length * 50, 3000))
  }

  render({sendMessage, message: {from, text, options, loadedMessages}}) {
    let response = [], responseOptions = '';

    for (let i = 0; i < text.length && i <= loadedMessages; i++) {
      if (i == loadedMessages) {
        response.push(
          <div key={i} className='message watson-message watson-font'>
            <div class='typing-dot'></div>
            <div class='typing-dot'></div>
            <div class='typing-dot'></div>
          </div>
        );
      } else {
        response.push(
          <div
            key={i}
            className={`message ${from}-message watson-font`}
            dangerouslySetInnerHTML={{__html: text[i]}}
          ></div>
        );
      }
    }

    if (loadedMessages >= text.length && Array.isArray(options)) {
      responseOptions = options.map((option, index) => (
        <div 
          key={text.length + index} className={`message message-option watson-font`} 
          onClick={() => { sendMessage(option); }}
        >
          {option}
        </div>
      ));
    }
      
    return <div>
      {response}
      {responseOptions}
    </div>;
  }
}
