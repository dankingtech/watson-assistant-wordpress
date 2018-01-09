import React, { Component } from 'react';


export default class Message extends Component {
  shouldComponentUpdate() {
    return false;
  }

  render() {
    let {sendMessage, message: {from, text, options}} = this.props;

    return <div>
      <div
        className={`message ${from}-message watson-font`}
        dangerouslySetInnerHTML={{__html: text}}
      ></div>
      {
        Array.isArray(options) && options.map((option, index) => (
          <div className={`message message-option watson-font`} onClick={() => { sendMessage(option); }}>
            {option}
          </div>
        ))
      }
    </div>;
  }
}
