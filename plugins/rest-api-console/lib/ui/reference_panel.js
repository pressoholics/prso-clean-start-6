var Component = require('./component'),
    $ = Component.$,
    ListComponent = require('./list_component'),
    OptionSelector = require('./option_selector'),
    fn = require('fn');

var ReferencePanel = Component.extend({
  'openClass'       : 'open',
  'browserClass'    : 'browser',
  'history'         : [],
  'search_url'      : null,
  'filter'          : function(){ return arguments[0]; },
  'onSelect'        : function(){},
  'onLoadVersions'  : function(){},
  'onLoadEndpoints' : function(){},
  'init' : function(node, options) {
    this.onSearch = this.search.bind(this);
    this.onReference = this.partitionedEndpoints.bind(this);
    this.groupsList = new ListComponent({
      'tabindex' : '1',
      'className' : 'groups',
      'itemTemplate':function(item){
        return $('<li></li>').append($("<span></span>").text(item));
      }
    });

    this.endpointsList = new ListComponent({
      'tabindex' : '1',
      'className' : 'endpoints',
      'itemTemplate' : function(item) {
        return $('<li></li>')
                  .append($("<span></span>").text(item.method + " " + (item.path_labeled || "?")).addClass('path'))
                  .append($("<span></span>").text(item.description).addClass('description'));
      }
    });

    this.groupOption = new OptionSelector({
      defaultValue: "all"
    });

    this.versionOption = new OptionSelector(this.options.versionSelector, {
      tabIndex: '2',
      label: function(v) { return 'v' + v; },
      optionLabel: function(v) { return 'v' + v; }
    });

    this.header = $("<header></header>").appendTo(this.node);
    this.browser = $("<div></div>").addClass(this.options.browserClass).appendTo(this.node);
    this.header
      .append($("<h2></h2>").text("API Reference"))
      .append($("<div></div>").append($('<span></span>').text('Group')).append(this.groupOption.node));

    // this.groupsList.on('select', this.updateEndpoints.bind(this));
    this.groupOption.on('change', this.updateEndpoints.bind(this));
    // this.groupsList.on('submit', this.endpointsList.focus.bind(this.endpointsList));

    this.endpointsList.on('select', this.updateDetail.bind(this));
    this.endpointsList.on('submit', this.selectEndpoint.bind(this));

    // this.groupsList.node.appendTo($('<div></div>').appendTo(this.browser));

    this.endpointsList.node.appendTo($('<div></div>').appendTo(this.browser));
    this.detail = $('<div></div>').appendTo(this.browser);
    this.node.addClass(this.options.openClass);

    // refresh the selected version's help resources when version is changed
    this.versionOption.on('change', (function(version) {
      this.loadingEndpoints = true;
      this.options.onLoadEndpoints(version, fn.arglock(this.onIndex, version).bind(this));
    }).bind(this));

    this.versionOption.node.hide();

    this.ranker = new Worker(this.options.search_url || "search.js");
    this.ranker.onmessage = (function(e) {
      console.log("Results", e.data.ms);
      this.onRanking(e.data.results);
    }).bind(this);

  },
  'onChange' : function(value, oldValue) {
    var groups = [],
        group_endpoints = {};

    var filtered = this.getEndpoints();

    _.each(filtered, function (endpoints, route) {
      var group = endpoints.namespace || "";

      if (groups.indexOf(group) === -1) {
        groups.push(group);
      }

      if (!group_endpoints[group]) {
        group_endpoints[group] = {};
      }

      group_endpoints[group][route] = endpoints;
    });

    groups.sort(function(a, b) {
      var a_l = a.toLowerCase(),
          b_l = b.toLowerCase();

      if (a_l == b_l) return 0;
      return a_l < b_l ? -1 : 1;
    });

    groups.unshift('all');
    group_endpoints.all = filtered;
    this.groups = groups;
    this.group_endpoints = group_endpoints;

    this.groupOption.setOptions(groups);
    this.groupsList.setValue(groups);
    this.endpointsList.setValue(filtered);

  }
});

ReferencePanel.prototype.load = function() {
  this.options.onLoadVersions(this.onVersions.bind(this));
};

ReferencePanel.prototype.refresh = function() {
  var endpoints = this.getValue();

  this.reset();
  this.setValue(endpoints);

};

ReferencePanel.prototype.toggle = function() {
  this.node.toggleClass(this.options.openClass);

  if (this.node.hasClass(this.options.openClass)) {
    this.onOpen();
  } else {
    this.onClose();
  }

};

ReferencePanel.prototype.isOpen = function() {
  return this.node.hasClass(this.options.openClass);
};


ReferencePanel.prototype.close = function() {
  if (this.isOpen()) {
    this.toggle();
  }
};

ReferencePanel.prototype.open = function() {
  if (!this.isOpen()) {
    this.toggle();
  }
};

ReferencePanel.prototype.onOpen = function() {
  this.groupsList.enable();
  this.endpointsList.enable();
  this.endpointsList.node.focus();
};

ReferencePanel.prototype.onClose = function() {
  this.groupsList.disable();
  this.endpointsList.disable();  
};

ReferencePanel.prototype.getEndpoints = function() {
  return this.options.filter(this.getValue());
};

ReferencePanel.prototype.partitionedEndpoints = function() {
  var all = [],
      groups = this.groups || [],
      grouped = this.group_endpoints;
  $.each(groups, function(i, group) {
    if (group == 'all') return;
    all.push(group);
    _.each(grouped[group], function (route_data, route) {
      route_data.methods.forEach(function (method) {
        var data = {
          method: method,
          version: "2",
          path_labeled: route,
          description: ""
        };
        all.push(data);
      });
    });
  });

  if(this.options.history.length > 0) {
    all = [{history: true, label: 'Recent'}].concat(this.options.history).concat(all);
  }
  return all;
};

ReferencePanel.prototype.updateEndpoints = function(group) {
  var endpoints = this.group_endpoints[group];
  this.endpointsList.setValue(endpoints);
};

ReferencePanel.prototype.updateDetail = function(endpoint) {
  this.detail.children().remove();
  $('<pre></pre>').appendTo(this.detail).text(JSON.stringify(endpoint, null, "  "));
};

ReferencePanel.prototype.search = function(q, callback) {

  if (this.loadingEndpoints) {
    callback(this.partitionedEndpoints().concat([{loading:true}]));
    this.onLoadingComplete = fn.arglock(this.search, q, callback).bind(this);
    return;
  }

  delete this.onLoadingComplete;

  if (q === "" || q === null) {
    callback(this.partitionedEndpoints());
    return;
  }

  this.ranker.postMessage({query: q, index: this.getEndpoints()});
  this.onRanking = callback;

};

ReferencePanel.prototype.selectEndpoint = function(endpoint) {
  this.options.onSelect(endpoint);
};

ReferencePanel.prototype.onVersions = function(err, versions, defaultVersion) {

  this.versionOption.disable();
  this.versionOption.setOptions(versions);
  this.versionOption.enable();
  this.versionOption.setValue(defaultVersion);
  this.versionOption.node.toggle(versions.length > 1);

};

ReferencePanel.prototype.onIndex = function (version, err, index) {
  this.setValue(index.routes);
  this.loadingEndpoints = false;
  if (this.onLoadingComplete) this.onLoadingComplete();
};

ReferencePanel.prototype.setHistory = function(endpoints) {
  this.options.history = endpoints;
};

ReferencePanel.prototype.addHistory = function(endpoint) {
  if (!this.options.history) this.options.history = [];

  this.options.history = this.options.history.filter(function(item) {
    return !(item.path_labeled == endpoint.path_labeled && item.method == endpoint.method);
  }).slice(0, 10);

  this.options.history.unshift(endpoint);

  this.emit('history', this.options.history);

};

module.exports = ReferencePanel;
