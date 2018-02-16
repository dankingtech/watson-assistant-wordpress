import React, { Component } from 'react';


export default class Message extends Component {
  shouldComponentUpdate() {
    return false;
  }

  render({sendMessage, message: {from, text, options}}) {
    let response, responseOptions = '';

    if (Array.isArray(text)) {
      response = text.map((message, index) => (
        <div
          key={index}
          className={`message ${from}-message watson-font`}
          dangerouslySetInnerHTML={{__html: message}}
        ></div>
      ));
    } else {
      response = (
        <div
          className={`message ${from}-message watson-font`}
          dangerouslySetInnerHTML={{__html: text}}
        ></div>
      );
    }

    if (Array.isArray(options)) {
      responseOptions = options.map((option, index) => (
        <div 
          key={index} className={`message message-option watson-font`} 
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
