import React, { useState } from 'react';
import { Clock, HelpCircle, CheckCircle2, ChevronLeft, ChevronRight, Check } from 'lucide-react';

export default function AssessmentPage() {
  const [selectedOption, setSelectedOption] = useState<number | null>(2);

  // Mock data for the options
  const options = [
    { id: 1, text: "Implementing an inverted index paired with a distributed hash table" },
    { id: 2, text: "Using a master-slave replication model with eventual consistency" },
    { id: 3, text: "Employing a consistent hashing ring coupled with vector clocks" },
    { id: 4, text: "Setting up a monolithic SQL database with read replicas" },
  ];

  // Mock data for the question grid in the sidebar
  const questionGrid = Array.from({ length: 25 }, (_, i) => ({
    number: i + 1,
    status: 
      i === 4 ? 'current' : 
      i < 4 ? 'answered' : 
      i === 12 || i === 18 ? 'flagged' : 
      'unanswered'
  }));

  return (
    <div className="min-h-screen bg-slate-50 font-sans text-indigo-950 flex flex-col selection:bg-[#c6ff00] selection:text-indigo-900 overflow-hidden">
      
      {/* Top Header */}
      <header className="sticky top-0 bg-white/80 backdrop-blur-xl z-50 border-b border-slate-100 shadow-sm">
        {/* Progress Bar */}
        <div className="absolute top-0 left-0 w-full h-[3px] bg-slate-100">
          <div className="h-full bg-[#c6ff00] w-[20%] transition-all duration-500 ease-in-out shadow-[0_0_10px_#c6ff00]"></div>
        </div>

        <div className="max-w-[1400px] mx-auto px-8 h-20 flex justify-between items-center">
          {/* Logo & Assessment Title */}
          <div className="flex items-center gap-6">
            <div className="flex items-center gap-2">
              <div className="w-8 h-8 rounded-lg bg-indigo-900 flex items-center justify-center shadow-lg shadow-indigo-900/20">
                <div className="w-3 h-3 rounded-full bg-[#c6ff00]"></div>
              </div>
              <span className="text-xl font-black text-indigo-950 tracking-tight">Saasmo.</span>
            </div>
            <div className="w-px h-8 bg-slate-200"></div>
            <div>
              <h1 className="text-sm font-bold text-indigo-900">Senior Backend Engineer Assessment</h1>
              <p className="text-xs font-medium text-slate-500">System Architecture Design</p>
            </div>
          </div>

          {/* Right Header Panel */}
          <div className="flex items-center gap-6">
            <button className="flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-indigo-900 transition-colors">
              <HelpCircle size={18} />
              <span>Support</span>
            </button>
            <div className="flex items-center gap-3 bg-white border border-slate-200 pl-4 pr-5 py-2.5 rounded-full shadow-sm">
              <Clock size={18} className="text-indigo-500 animate-pulse" />
              <span className="font-mono font-bold text-indigo-950 text-lg tracking-tight">42:15</span>
              <span className="text-xs font-semibold text-slate-400 uppercase tracking-widest ml-1">Left</span>
            </div>
          </div>
        </div>
      </header>

      {/* Main Layout Area */}
      <main className="flex-1 max-w-[1400px] w-full mx-auto px-8 py-10 flex gap-10">
        
        {/* Left Column: Question Container */}
        <div className="flex-1 flex flex-col">
          
          {/* Question Header */}
          <div className="mb-8 flex justify-between items-end">
            <div>
              <span className="inline-block px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-full mb-3 uppercase tracking-wider">Question 5 of 25</span>
              <h2 className="text-3xl font-extrabold text-indigo-950 leading-tight">
                Which architectural approach best ensures high availability and partition tolerance in a distributed key-value store?
              </h2>
            </div>
          </div>

          {/* Question Options */}
          <div className="flex-1 flex flex-col gap-4">
            {options.map((option) => {
              const isSelected = selectedOption === option.id;
              return (
                <button
                  key={option.id}
                  onClick={() => setSelectedOption(option.id)}
                  className={`relative flex items-center p-6 rounded-[24px] text-left transition-all duration-200 border-2 ${
                    isSelected 
                      ? 'bg-indigo-50/50 border-indigo-600 shadow-md shadow-indigo-100 translate-x-2' 
                      : 'bg-white border-white hover:border-slate-200 hover:shadow-sm shadow-sm'
                  }`}
                >
                  {/* Selected Tick Indicator */}
                  <div className={`w-6 h-6 rounded-full flex items-center justify-center mr-5 shrink-0 transition-colors ${
                    isSelected ? 'bg-[#c6ff00] text-indigo-950' : 'bg-slate-100 text-transparent border border-slate-200'
                  }`}>
                    <Check size={14} strokeWidth={4} />
                  </div>
                  
                  {/* Option Text */}
                  <span className={`text-lg font-medium leading-snug ${
                    isSelected ? 'text-indigo-950' : 'text-slate-600'
                  }`}>
                    {option.text}
                  </span>
                </button>
              );
            })}
          </div>

          {/* Action Footer */}
          <div className="mt-10 flex justify-between items-center pt-8 border-t border-slate-200">
            <button className="flex items-center gap-2 text-slate-500 hover:text-indigo-900 font-semibold transition-colors px-4 py-2">
              <ChevronLeft size={20} />
              Previous
            </button>
            <div className="flex items-center gap-4">
              <label className="flex items-center gap-2 cursor-pointer group">
                <input type="checkbox" className="w-4 h-4 rounded text-indigo-600 bg-slate-100 border-slate-300 focus:ring-indigo-500 cursor-pointer" />
                <span className="text-sm font-semibold text-slate-500 group-hover:text-indigo-900 transition-colors">Flag for review</span>
              </label>
              <button className="bg-indigo-950 text-white px-8 py-3.5 rounded-full font-bold text-sm tracking-wide hover:bg-indigo-800 transition-all shadow-xl shadow-indigo-950/20 flex items-center gap-2 group">
                Next Question
                <ChevronRight size={18} className="group-hover:translate-x-1 transition-transform" />
              </button>
            </div>
          </div>
        </div>

        {/* Right Column: Navigator Sidebar */}
        <aside className="w-[340px] shrink-0">
          <div className="bg-white rounded-[32px] p-8 shadow-sm border border-slate-100 sticky top-[120px]">
            <div className="flex justify-between items-center mb-6">
              <h3 className="font-bold text-indigo-950 text-lg">Question Navigator</h3>
            </div>
            
            {/* Grid */}
            <div className="grid grid-cols-5 gap-3">
              {questionGrid.map((q) => {
                let boxStyle = "bg-slate-50 border-slate-100 text-slate-500 hover:border-slate-300"; // unanswered
                let dotIndicator = null;

                if (q.status === 'current') {
                  boxStyle = "bg-indigo-950 border-indigo-950 text-[#c6ff00] shadow-md shadow-indigo-950/20 scale-110 z-10 font-bold";
                } else if (q.status === 'answered') {
                  boxStyle = "bg-indigo-50 border-indigo-100 text-indigo-700 font-semibold";
                  dotIndicator = <div className="absolute -top-1 -right-1 w-2.5 h-2.5 bg-[#c6ff00] rounded-full border-2 border-white shadow-sm"></div>;
                } else if (q.status === 'flagged') {
                  boxStyle = "bg-orange-50 border-orange-200 text-orange-700 font-semibold";
                  dotIndicator = <div className="absolute -top-1 -right-1 w-2.5 h-2.5 bg-amber-500 rounded-full border-2 border-white shadow-sm"></div>;
                }

                return (
                  <button 
                    key={q.number}
                    className={`relative w-full aspect-square rounded-xl border flex items-center justify-center text-sm transition-all duration-200 ${boxStyle}`}
                  >
                    {q.number}
                    {dotIndicator}
                  </button>
                );
              })}
            </div>

            {/* Legend */}
            <div className="mt-8 pt-6 border-t border-slate-100 flex flex-col gap-3 text-xs font-semibold text-slate-500">
              <div className="flex items-center gap-3">
                <div className="w-3 h-3 rounded bg-indigo-50 border border-indigo-100 relative">
                    <div className="absolute -top-1 -right-1 w-2 h-2 bg-[#c6ff00] rounded-full border border-white"></div>
                </div>
                <span>Answered</span>
              </div>
              <div className="flex items-center gap-3">
                <div className="w-3 h-3 rounded bg-orange-50 border border-orange-200 relative">
                  <div className="absolute -top-1 -right-1 w-2 h-2 bg-amber-500 rounded-full border border-white"></div>
                </div>
                <span>Flagged for review</span>
              </div>
              <div className="flex items-center gap-3">
                <div className="w-3 h-3 rounded bg-slate-50 border border-slate-100"></div>
                <span>Not answered</span>
              </div>
            </div>

            {/* Submit Section */}
            <div className="mt-10">
              <button className="w-full bg-[#c6ff00] text-indigo-950 font-bold py-3.5 rounded-full hover:bg-[#b3e600] transition-colors shadow-lg shadow-[#c6ff00]/20 flex items-center justify-center gap-2">
                <CheckCircle2 size={18} />
                Submit Assessment
              </button>
              <p className="text-center text-[11px] text-slate-400 mt-3 font-medium">You have 20 unanswered questions</p>
            </div>
          </div>
        </aside>

      </main>
    </div>
  );
}